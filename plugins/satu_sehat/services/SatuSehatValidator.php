<?php

namespace Plugins\Satu_Sehat\Services;

/**
 * Validasi entri Bundle ERM Rawat Jalan sebelum dikirim ke SATUSEHAT.
 */
class SatuSehatValidator
{
  /**
   * Validasi daftar entri bundle.
   *
   * @param array $entries Entri hasil resource builder (fullUrl + resource).
   * @return array{valid:bool,errors:array<string>,warnings:array<string>}
   */
  public function validate(array $entries)
  {
    $errors = [];
    $warnings = [];

    $entries = array_values(array_filter($entries, fn($e) => is_array($e) && isset($e['resource'])));
    if (empty($entries)) {
      return ['valid' => false, 'errors' => ['Tidak ada resource yang akan dikirim.'], 'warnings' => []];
    }

    $fullUrls = [];
    $encounters = 0;
    foreach ($entries as $entry) {
      $fullUrl = (string) ($entry['fullUrl'] ?? '');
      if ($fullUrl === '') {
        $errors[] = 'Entri ' . ($entry['resource']['resourceType'] ?? '?') . ' tanpa fullUrl.';
      } elseif (in_array($fullUrl, $fullUrls, true)) {
        $errors[] = 'fullUrl duplikat: ' . $fullUrl;
      } else {
        $fullUrls[] = $fullUrl;
      }

      $resource = (array) $entry['resource'];
      $type = (string) ($resource['resourceType'] ?? '');
      if ($type === '') {
        $errors[] = 'Resource tanpa resourceType pada ' . $fullUrl;
        continue;
      }

      switch ($type) {
        case 'Encounter':
          $encounters++;
          $this->validateEncounter($resource, $errors);
          break;
        case 'Condition':
          if (empty($resource['code']['coding']) && trim((string) ($resource['code']['text'] ?? '')) === '') {
            $warnings[] = 'Condition tanpa kode/text diagnosis.';
          }
          $this->requireReference($resource, 'subject', 'Patient', $fullUrl, $errors);
          break;
        case 'Observation':
          if (($resource['status'] ?? '') === '') {
            $errors[] = 'Observation tanpa status (' . $fullUrl . ').';
          }
          $hasValue = !empty($resource['valueQuantity']) || !empty($resource['valueString'])
            || !empty($resource['valueCodeableConcept']) || !empty($resource['valueBoolean'])
            || !empty($resource['component']) || !empty($resource['dataAbsentReason']);
          if (!$hasValue) {
            $warnings[] = 'Observation tanpa nilai hasil (' . $fullUrl . ').';
          }
          break;
        case 'MedicationRequest':
          if (empty($resource['medicationReference'])) {
            $errors[] = 'MedicationRequest tanpa medicationReference (' . $fullUrl . ').';
          }
          if (empty($resource['dosageInstruction'])) {
            $warnings[] = 'MedicationRequest tanpa dosageInstruction (' . $fullUrl . ').';
          }
          break;
        case 'Medication':
          if (empty($resource['code']['coding'])) {
            $warnings[] = 'Medication tanpa kode KFA (' . $fullUrl . ').';
          }
          break;
        case 'AllergyIntolerance':
          // AllergyIntolerance memakai field "patient", bukan "subject".
          $this->requireReference($resource, 'patient', 'Patient', $fullUrl, $errors);
          break;
        case 'ServiceRequest':
        case 'DiagnosticReport':
        case 'Procedure':
        case 'CarePlan':
        case 'MedicationDispense':
        case 'QuestionnaireResponse':
        case 'ClinicalImpression':
        case 'MedicationStatement':
        case 'MedicationAdministration':
        case 'Specimen':
        case 'RiskAssessment':
        case 'Goal':
          $this->requireReference($resource, 'subject', 'Patient', $fullUrl, $errors);
          break;
        case 'FamilyMemberHistory':
        case 'NutritionOrder':
          // Dua resource ini memakai field "patient", bukan "subject".
          $this->requireReference($resource, 'patient', 'Patient', $fullUrl, $errors);
          break;
      }
    }

    if ($encounters === 0) {
      $errors[] = 'Bundle ERM wajib memuat minimal satu Encounter.';
    } elseif ($encounters > 1) {
      $warnings[] = 'Bundle memuat lebih dari satu Encounter (' . $encounters . ').';
    }

    // Setiap referensi urn:uuid harus ada di dalam bundle.
    foreach ($entries as $entry) {
      $json = json_encode($entry['resource']);
      if (preg_match_all('/urn:uuid:[a-f0-9-]+/i', (string) $json, $m)) {
        foreach ($m[0] as $ref) {
          if (!in_array($ref, $fullUrls, true)) {
            $errors[] = 'Referensi ' . $ref . ' tidak ditemukan di dalam bundle (' . $entry['fullUrl'] . ').';
          }
        }
      }
    }

    return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings];
  }

  private function validateEncounter(array $resource, array &$errors)
  {
    $fullUrl = 'Encounter';
    if (empty($resource['status'])) {
      $errors[] = 'Encounter tanpa status.';
    }
    if (empty($resource['class']['code'])) {
      $errors[] = 'Encounter tanpa class (AMB/IMP/EMER).';
    }
    $this->requireReference($resource, 'subject', 'Patient', $fullUrl, $errors);
    if (empty($resource['period']['start'])) {
      $errors[] = 'Encounter tanpa period.start.';
    }
    if (empty($resource['location'][0]['location']['reference'])) {
      $errors[] = 'Encounter tanpa lokasi (Location).';
    }
    if (empty($resource['participant'][0]['individual']['reference'])) {
      $errors[] = 'Encounter tanpa peserta (Practitioner).';
    }
    if (empty($resource['serviceProvider']['reference'])) {
      $errors[] = 'Encounter tanpa serviceProvider (Organization).';
    }
  }

  private function requireReference(array $resource, $field, $expectedType, $fullUrl, array &$errors)
  {
    $ref = (string) ($resource[$field]['reference'] ?? '');
    if ($ref === '' || preg_match('#^[A-Za-z]+/$#', $ref)) {
      $errors[] = $field . ' ' . $expectedType . ' belum terpetakan (' . $fullUrl . ').';
      return;
    }
    if (strpos($ref, 'urn:uuid:') !== 0 && strpos($ref, $expectedType . '/') !== 0) {
      $errors[] = $field . ' harus mereferensikan ' . $expectedType . ' (' . $fullUrl . ').';
    }
  }

  /**
   * Validasi sebuah Bundle siap kirim (wrapper untuk isi entry).
   */
  public function validateBundle(array $bundle)
  {
    $entries = is_array($bundle['entry'] ?? null) ? $bundle['entry'] : [];
    return $this->validate($entries);
  }
}
