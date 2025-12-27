import React, { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useNavigate, useParams } from 'react-router-dom';
import { patientService } from '../services/patientService';
import type { Patient } from '../types';
import { ArrowLeft, Save } from 'lucide-react';

// Schema validation
const patientSchema = z.object({
  nm_pasien: z.string().min(1, 'Name is required'),
  no_ktp: z.string().min(1, 'KTP Number is required'),
  jk: z.enum(['L', 'P'], { errorMap: () => ({ message: 'Gender is required' }) }),
  tgl_lahir: z.string().min(1, 'Birth date is required'),
  alamat: z.string().min(1, 'Address is required'),
  no_tlp: z.string().min(1, 'Phone number is required'),
  kd_pj: z.string().min(1, 'Payment type is required'),
});

type PatientFormInputs = z.infer<typeof patientSchema>;

export default function PatientForm() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const isEditMode = !!id;
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const { register, handleSubmit, reset, setValue, formState: { errors, isSubmitting } } = useForm<PatientFormInputs>({
    resolver: zodResolver(patientSchema),
    defaultValues: {
      jk: 'L', // Default gender
      kd_pj: 'UMUM', // Default payment
    }
  });

  useEffect(() => {
    if (isEditMode && id) {
      setLoading(true);
      patientService.getPatient(id)
        .then((data) => {
          // Reset form with fetched data
          // Ensure fields match schema
          setValue('nm_pasien', data.nm_pasien);
          setValue('no_ktp', data.no_ktp);
          setValue('jk', data.jk);
          setValue('tgl_lahir', data.tgl_lahir);
          setValue('alamat', data.alamat);
          setValue('no_tlp', data.no_tlp);
          setValue('kd_pj', data.kd_pj);
        })
        .catch((err) => {
          console.error(err);
          setError('Failed to fetch patient details');
        })
        .finally(() => setLoading(false));
    }
  }, [isEditMode, id, setValue]);

  const onSubmit = async (data: PatientFormInputs) => {
    try {
      setError(null);
      // Add default values for required fields that might be missing in simple form
      const payload = {
        ...data,
        // Add defaults that backend might expect if not handled there
        nm_ibu: '-',
        gol_darah: '-',
        pekerjaan: '-',
        stts_nikah: 'BELUM MENIKAH',
        agama: 'ISLAM',
        pnd: '-',
        keluarga: 'AYAH',
        namakeluarga: '-',
        kd_kel: '1',
        kd_kec: '1',
        kd_kab: '1',
        pekerjaanpj: '-',
        alamatpj: data.alamat,
        kelurahanpj: '-',
        kecamatanpj: '-',
        kabupatenpj: '-',
        perusahaan_pasien: '-',
        suku_bangsa: '1',
        bahasa_pasien: '1',
        cacat_fisik: '1',
        email: '-',
        nip: '-',
        kd_prop: '1',
        propinsipj: '-',
      };

      if (isEditMode && id) {
        await patientService.updatePatient(id, payload);
      } else {
        await patientService.createPatient(payload as any);
      }
      navigate('/patients');
    } catch (err: any) {
      console.error(err);
      setError(err.response?.data?.message || 'Failed to save patient');
    }
  };

  if (loading) return <div className="p-6">Loading...</div>;

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">
          {isEditMode ? 'Edit Patient' : 'Add New Patient'}
        </h1>
        <button
          onClick={() => navigate('/patients')}
          className="flex items-center text-gray-600 hover:text-gray-900"
        >
          <ArrowLeft className="h-5 w-5 mr-1" />
          Back to List
        </button>
      </div>

      {error && (
        <div className="bg-red-50 text-red-600 p-4 rounded-md">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="bg-white shadow sm:rounded-lg p-6 space-y-6">
        
        {/* Name */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Name</label>
          <input
            type="text"
            {...register('nm_pasien')}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.nm_pasien && <p className="mt-1 text-sm text-red-600">{errors.nm_pasien.message}</p>}
        </div>

        {/* KTP */}
        <div>
          <label className="block text-sm font-medium text-gray-700">KTP Number</label>
          <input
            type="text"
            {...register('no_ktp')}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.no_ktp && <p className="mt-1 text-sm text-red-600">{errors.no_ktp.message}</p>}
        </div>

        {/* Gender */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Gender</label>
          <select
            {...register('jk')}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          >
            <option value="L">Male (L)</option>
            <option value="P">Female (P)</option>
          </select>
          {errors.jk && <p className="mt-1 text-sm text-red-600">{errors.jk.message}</p>}
        </div>

        {/* Birth Date */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Birth Date</label>
          <input
            type="date"
            {...register('tgl_lahir')}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.tgl_lahir && <p className="mt-1 text-sm text-red-600">{errors.tgl_lahir.message}</p>}
        </div>

        {/* Phone */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Phone</label>
          <input
            type="text"
            {...register('no_tlp')}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.no_tlp && <p className="mt-1 text-sm text-red-600">{errors.no_tlp.message}</p>}
        </div>

        {/* Payment Type */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Payment Type (Kode Penjab)</label>
          <input
            type="text"
            {...register('kd_pj')}
            placeholder="e.g. UMUM, BPJS"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.kd_pj && <p className="mt-1 text-sm text-red-600">{errors.kd_pj.message}</p>}
        </div>

        {/* Address */}
        <div>
          <label className="block text-sm font-medium text-gray-700">Address</label>
          <textarea
            {...register('alamat')}
            rows={3}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
          />
          {errors.alamat && <p className="mt-1 text-sm text-red-600">{errors.alamat.message}</p>}
        </div>

        <div className="flex justify-end pt-4">
          <button
            type="button"
            onClick={() => navigate('/patients')}
            className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={isSubmitting}
            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <Save className="h-5 w-5 mr-2" />
            {isSubmitting ? 'Saving...' : 'Save Patient'}
          </button>
        </div>
      </form>
    </div>
  );
}
