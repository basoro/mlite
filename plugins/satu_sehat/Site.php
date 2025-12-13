<?php

namespace Plugins\Satu_Sehat;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('satu-sehat/encounter/(:any)', 'forwardEncounter');
        $this->route('satu-sehat/condition/(:any)', 'forwardCondition');
        $this->route('satu-sehat/observation/(:any)/(:any)', 'forwardObservation');
        $this->route('satu-sehat/procedure/(:any)', 'forwardProcedure');
        $this->route('satu-sehat/diet-gizi/(:any)', 'forwardDietGizi');
        $this->route('satu-sehat/vaksin/(:any)', 'forwardVaksin');
        $this->route('satu-sehat/care-plan/(:any)', 'forwardCarePlan');
        $this->route('satu-sehat/clinical-impression/(:any)', 'forwardClinicalImpression');
        $this->route('satu-sehat/medication/(:any)/(:any)', 'forwardMedication');
        $this->route('satu-sehat/medication/(:any)', 'forwardMedication');
        $this->route('satu-sehat/laboratory/(:any)/(:any)', 'forwardLaboratory');
        $this->route('satu-sehat/laboratory/(:any)', 'forwardLaboratory');
        $this->route('satu-sehat/radiology/(:any)/(:any)', 'forwardRadiology');
        $this->route('satu-sehat/radiology/(:any)', 'forwardRadiology');
        $this->route('satu-sehat/forward-norawat/(:any)', 'forwardByNoRawat');
        $this->route('satu-sehat/forward-norawat', 'forwardByNoRawat');
        $this->route('satu-sehat/forward-tanggal/(:any)', 'forwardByDate');
        $this->route('satu-sehat/forward-tanggal', 'forwardByDate');
    }

    public function forwardEncounter($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getEncounter($no_rawat, false);
    }

    public function forwardCondition($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getCondition($no_rawat, false);
    }

    public function forwardObservation($no_rawat = null, $ttv = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($ttv === null && isset($_GET['ttv'])) {
            $ttv = $_GET['ttv'];
        }
        if ($no_rawat === null || $ttv === null) {
            echo json_encode(['error' => 'parameter kosong', 'no_rawat' => $no_rawat, 'ttv' => $ttv]);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getObservation($no_rawat, $ttv, false);
    }

    public function forwardProcedure($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getProcedure($no_rawat, false);
    }

    public function forwardDietGizi($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getDietGizi($no_rawat, false);
    }

    public function forwardVaksin($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getVaksin($no_rawat, false);
    }

    public function forwardClinicalImpression($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getClinicalImpression($no_rawat, false);
    }

    public function forwardMedication($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'request';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getMedication((string)$no_rawat, (string)$tipe, false);
    }

    public function forwardCarePlan($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getCarePlan($no_rawat, false);
    }

    public function forwardLaboratory($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'request';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getLaboratory((string)$no_rawat, (string)$tipe, false);
    }

    public function forwardRadiology($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'request';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getRadiology((string)$no_rawat, (string)$tipe, false);
    }

    public function forwardByDate($tanggal = null)
    {
        if ($tanggal === null && isset($_GET['tanggal'])) {
            $tanggal = $_GET['tanggal'];
        }
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $rows = $this->db('reg_periksa')
            ->select('no_rawat')
            ->where('reg_periksa.tgl_registrasi', $tanggal)
            ->where('stts', '!=', 'Batal')
            ->toArray();
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'display' => $r['no_rawat'],
                'url' => str_replace('/', '', $r['no_rawat'])
            ];
        }
        $encBase = '/satu-sehat/encounter/';
        $condBase = '/satu-sehat/condition/';
        $obsBase = '/satu-sehat/observation/';
        $procBase = '/satu-sehat/procedure/';
        $impBase = '/satu-sehat/clinical-impression/';
        $vaxBase = '/satu-sehat/vaksin/';
        $dietBase = '/satu-sehat/diet-gizi/';
        $careBase = '/satu-sehat/care-plan/';
        $medBase = '/satu-sehat/medication/';
        $labBase = '/satu-sehat/laboratory/';
        $radBase = '/satu-sehat/radiology/';

        echo '<!doctype html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>Forward Satu Sehat</title>
        <style>
            body { font-family: system-ui, Arial, sans-serif; }
            .item { border:1px solid #ddd; padding:8px; margin:8px 0; }
            .label { font-weight:bold; }
            pre { white-space:pre-wrap; word-wrap:break-word; background:#f7f7f7; padding:8px; border-radius:4px; }
            .summary { margin-top:16px; border-top:2px solid #ccc; padding-top:12px; }
        </style>
        </head>

        <body>

        <form method="get" action="/satu-sehat/forward-tanggal" style="margin-bottom:12px"><label>Tanggal : </label> <input type="date" name="tanggal" value="' . htmlspecialchars($tanggal, ENT_QUOTES) . '" /> <button type="submit">Proses</button></form>
        <h3>Proses tanggal ' . $tanggal . '</h3>

        <div id="log"></div>

        <div class="summary">
            <div class="label">Ringkasan JSON</div>
            <pre id="summary"></pre>
        </div>

        <script>
        const list = ' . json_encode($list) . ';
        const ttv = ["tensi", "nadi", "respirasi", "suhu", "spo2", "gcs", "kesadaran", "berat", "tinggi", "perut"];

        async function call(url) {
            try {
                const r = await fetch(url);
                return await r.text();
            } catch (e) {
                return String(e);
            }
        }

        function toJsonOrString(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                try {
                    return JSON.parse(text.replace(/`/g, ""));
                } catch (e2) {
                    return text;
                }
            }
        }

        async function run() {
            const log = document.getElementById("log");
            const summary = document.getElementById("summary");
            const results = [];

            for (const item of list) {
                const nrDisp = item.display;
                const nrUrl = item.url;

                const container = document.createElement("div");
                container.className = "item";
                container.innerHTML = `<div class="label">No Rawat: ${nrDisp}</div>`;
                log.appendChild(container);

                // Encounter
                const encTxt = await call("' . $encBase . '" + nrUrl);
                const enc = toJsonOrString(encTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Encounter:<pre>" + (typeof enc === "string" ? enc : JSON.stringify(enc, null, 2)) + "</pre></div>"
                );

                // Condition
                const condTxt = await call("' . $condBase . '" + nrUrl);
                const cond = toJsonOrString(condTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Condition:<pre>" + (typeof cond === "string" ? cond : JSON.stringify(cond, null, 2)) + "</pre></div>"
                );

                // Observations
                const obsRes = {};
                for (const t of ttv) {
                    const obsTxt = await call("' . $obsBase . '" + nrUrl + "/" + t);
                    const obs = toJsonOrString(obsTxt);
                    obsRes[t] = obs;

                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Observation (" + t + "):<pre>" +
                            (typeof obs === "string" ? obs : JSON.stringify(obs, null, 2)) +
                        "</pre></div>"
                    );
                }

                // Procedure
                const procTxt = await call("' . $procBase . '" + nrUrl);
                const proc = toJsonOrString(procTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Procedure:<pre>" + (typeof proc === "string" ? proc : JSON.stringify(proc, null, 2)) + "</pre></div>"
                );

                // Clinical Impression
                const impTxt = await call("' . $impBase . '" + nrUrl);
                const imp = toJsonOrString(impTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Clinical Impression:<pre>" + (typeof imp === "string" ? imp : JSON.stringify(imp, null, 2)) + "</pre></div>"
                );

                // Vaksin
                const vaxTxt = await call("' . $vaxBase . '" + nrUrl);
                const vax = toJsonOrString(vaxTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Vaksin:<pre>" + (typeof vax === "string" ? vax : JSON.stringify(vax, null, 2)) + "</pre></div>"
                );

                // Diet Gizi
                const dietTxt = await call("' . $dietBase . '" + nrUrl);
                const diet = toJsonOrString(dietTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Diet Gizi:<pre>" + (typeof diet === "string" ? diet : JSON.stringify(diet, null, 2)) + "</pre></div>"
                );

                // Care Plan
                const careTxt = await call("' . $careBase . '" + nrUrl);
                const care = toJsonOrString(careTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Care Plan:<pre>" + (typeof care === "string" ? care : JSON.stringify(care, null, 2)) + "</pre></div>"
                );

                // Medication (request/dispense/statement)
                const medTypes = ["request", "dispense", "statement"];
                const medRes = {};
                for (const mt of medTypes) {
                    const mtTxt = await call("' . $medBase . '" + nrUrl + "/" + mt);
                    const mtJson = toJsonOrString(mtTxt);
                    medRes[mt] = mtJson;
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Medication (" + mt + "):<pre>" + (typeof mtJson === "string" ? mtJson : JSON.stringify(mtJson, null, 2)) + "</pre></div>"
                    );
                }

                // Laboratory (request/specimen/result)
                const labTypes = ["request", "specimen", "observation", "diagnostic"];
                const labRes = {};
                for (const lt of labTypes) {
                    const ltTxt = await call("' . $labBase . '" + nrUrl + "/" + lt);
                    const ltJson = toJsonOrString(ltTxt);
                    labRes[lt] = ltJson;
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Laboratory (" + lt + "):<pre>" + (typeof ltJson === "string" ? ltJson : JSON.stringify(ltJson, null, 2)) + "</pre></div>"
                    );
                }

                // Radiology (request/result)
                const radTypes = ["request", "specimen", "observation", "diagnostic"];
                const radRes = {};
                for (const rt of radTypes) {
                    const rtTxt = await call("' . $radBase . '" + nrUrl + "/" + rt);
                    const rtJson = toJsonOrString(rtTxt);
                    radRes[rt] = rtJson;
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Radiology (" + rt + "):<pre>" + (typeof rtJson === "string" ? rtJson : JSON.stringify(rtJson, null, 2)) + "</pre></div>"
                    );
                }
                    
                results.push({
                    no_rawat: nrDisp,
                    encounter: enc,
                    condition: cond,
                    observation: obsRes,
                    procedure: proc,
                    clinical_impression: imp,
                    vaksin: vax,
                    diet_gizi: diet,
                    care_plan: care,
                    medication: medRes,
                    laboratory: labRes,
                    radiology: radRes
                });

                summary.textContent = JSON.stringify(results, null, 2);
            }

            summary.textContent = JSON.stringify(results, null, 2);
        }

        run();
        </script>

        </body>
        </html>';

        echo '</body></html>';
        exit();
    }



    public function forwardByNoRawat($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            $no_rawat = '';
        }
        $list = [];
        if ($no_rawat !== '') {
            $list[] = [
                'display' => $no_rawat,
                'url' => str_replace('/', '', $no_rawat)
            ];
        }

        $encBase = '/satu-sehat/encounter/';
        $condBase = '/satu-sehat/condition/';
        $obsBase = '/satu-sehat/observation/';
        $procBase = '/satu-sehat/procedure/';
        $impBase = '/satu-sehat/clinical-impression/';
        $vaxBase = '/satu-sehat/vaksin/';
        $dietBase = '/satu-sehat/diet-gizi/';
        $careBase = '/satu-sehat/care-plan/';
        $medBase = '/satu-sehat/medication/';
        $labBase = '/satu-sehat/laboratory/';
        $radBase = '/satu-sehat/radiology/';

        echo '<!doctype html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>Forward Satu Sehat</title>
        <style>
            body { font-family: system-ui, Arial, sans-serif; }
            .item { border:1px solid #ddd; padding:8px; margin:8px 0; }
            .label { font-weight:bold; }
            pre { white-space:pre-wrap; word-wrap:break-word; background:#f7f7f7; padding:8px; border-radius:4px; }
            .summary { margin-top:16px; border-top:2px solid #ccc; padding-top:12px; }
        </style>
        </head>

        <body>

        <h3>Proses no_rawat ' . $no_rawat . '</h3>

        <div id="log"></div>

        <div class="summary">
            <div class="label">Ringkasan JSON</div>
            <pre id="summary"></pre>
        </div>

        <script>
        const list = ' . json_encode($list) . ';
        const ttv = ["tensi", "nadi", "respirasi", "suhu", "spo2", "gcs", "kesadaran", "berat", "tinggi", "perut"];

        async function call(url) {
            try {
                const r = await fetch(url);
                return await r.text();
            } catch (e) {
                return String(e);
            }
        }

        function toJsonOrString(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                try {
                    return JSON.parse(text.replace(/`/g, ""));
                } catch (e2) {
                    return text;
                }
            }
        }

        async function run() {
            const log = document.getElementById("log");
            const summary = document.getElementById("summary");
            const results = [];

            for (const item of list) {
                const nrDisp = item.display;
                const nrUrl = item.url;

                const container = document.createElement("div");
                container.className = "item";
                container.innerHTML = `<div class="label">No Rawat: ${nrDisp}</div>`;
                log.appendChild(container);

                // Encounter
                const encTxt = await call("' . $encBase . '" + nrUrl);
                const enc = toJsonOrString(encTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Encounter:<pre>" + (typeof enc === "string" ? enc : JSON.stringify(enc, null, 2)) + "</pre></div>"
                );

                // Condition
                const condTxt = await call("' . $condBase . '" + nrUrl);
                const cond = toJsonOrString(condTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Condition:<pre>" + (typeof cond === "string" ? cond : JSON.stringify(cond, null, 2)) + "</pre></div>"
                );

                // Observations
                const obsRes = {};
                for (const t of ttv) {
                    const obsTxt = await call("' . $obsBase . '" + nrUrl + "/" + t);
                    const obs = toJsonOrString(obsTxt);
                    obsRes[t] = obs;

                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Observation (" + t + "):<pre>" +
                            (typeof obs === "string" ? obs : JSON.stringify(obs, null, 2)) +
                        "</pre></div>"
                    );
                }

                // Procedure
                const procTxt = await call("' . $procBase . '" + nrUrl);
                const proc = toJsonOrString(procTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Procedure:<pre>" + (typeof proc === "string" ? proc : JSON.stringify(proc, null, 2)) + "</pre></div>"
                );

                // Clinical Impression
                const impTxt = await call("' . $impBase . '" + nrUrl);
                const imp = toJsonOrString(impTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Clinical Impression:<pre>" + (typeof imp === "string" ? imp : JSON.stringify(imp, null, 2)) + "</pre></div>"
                );

                // Vaksin
                const vaxTxt = await call("' . $vaxBase . '" + nrUrl);
                const vax = toJsonOrString(vaxTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Vaksin:<pre>" + (typeof vax === "string" ? vax : JSON.stringify(vax, null, 2)) + "</pre></div>"
                );

                // Diet Gizi
                const dietTxt = await call("' . $dietBase . '" + nrUrl);
                const diet = toJsonOrString(dietTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Diet Gizi:<pre>" + (typeof diet === "string" ? diet : JSON.stringify(diet, null, 2)) + "</pre></div>"
                );

                // Care Plan
                const careTxt = await call("' . $careBase . '" + nrUrl);
                const care = toJsonOrString(careTxt);
                container.insertAdjacentHTML(
                    "beforeend",
                    "<div>Care Plan:<pre>" + (typeof care === "string" ? care : JSON.stringify(care, null, 2)) + "</pre></div>"
                );

                // Medication (request/dispense/statement)
                const medTypes = ["request", "dispense", "statement"];
                const medRes = {};
                for (const mt of medTypes) {
                    const mtTxt = await call("' . $medBase . '" + nrUrl + "/" + mt);
                    const mtJson = toJsonOrString(mtTxt);
                    medRes[mt] = mtJson;
                    console.log("' . $medBase . '" + nrUrl + "/" + mt);
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Medication (" + mt + "):<pre>" + (typeof mtJson === "string" ? mtJson : JSON.stringify(mtJson, null, 2)) + "</pre></div>"
                    );
                }

                // Laboratory (request/specimen/result)
                const labTypes = ["request", "specimen", "observation", "diagnostic"];
                const labRes = {};
                for (const lt of labTypes) {
                    const ltTxt = await call("' . $labBase . '" + nrUrl + "/" + lt);
                    const ltJson = toJsonOrString(ltTxt);
                    labRes[lt] = ltJson;
                    console.log("' . $labBase . '" + nrUrl + "/" + lt);
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Laboratory (" + lt + "):<pre>" + (typeof ltJson === "string" ? ltJson : JSON.stringify(ltJson, null, 2)) + "</pre></div>"
                    );
                }

                // Radiology (request/result)
                const radTypes = ["request", "specimen", "observation", "diagnostic"];
                const radRes = {};
                for (const rt of radTypes) {
                    const rtTxt = await call("' . $radBase . '" + nrUrl + "/" + rt);
                    const rtJson = toJsonOrString(rtTxt);
                    radRes[rt] = rtJson;
                    container.insertAdjacentHTML(
                        "beforeend",
                        "<div>Radiology (" + rt + "):<pre>" + (typeof rtJson === "string" ? rtJson : JSON.stringify(rtJson, null, 2)) + "</pre></div>"
                    );
                }
                    
                results.push({
                    no_rawat: nrDisp,
                    encounter: enc,
                    condition: cond,
                    observation: obsRes,
                    procedure: proc,
                    clinical_impression: imp,
                    vaksin: vax,
                    diet_gizi: diet,
                    care_plan: care,
                    medication: medRes,
                    laboratory: labRes,
                    radiology: radRes
                });

                summary.textContent = JSON.stringify(results, null, 2);
            }

            summary.textContent = JSON.stringify(results, null, 2);
        }

        run();
        </script>

        </body>
        </html>';

        echo '</body></html>';
        exit();
    }

}
