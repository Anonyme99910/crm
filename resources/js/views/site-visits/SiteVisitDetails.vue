<template>
  <div v-if="visit" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">معاينة - {{ visit.lead?.name }}</h2>
        <p class="text-gray-500">{{ formatDateTime(visit.scheduled_at) }}</p>
      </div>
      <span :class="statusClass(visit.status)" class="badge text-base px-4 py-2">{{ statusLabel(visit.status) }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل المعاينة</h3></div>
          <div class="card-body space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><span class="text-gray-500">العميل:</span> <span class="mr-2 font-medium">{{ visit.lead?.name }}</span></div>
              <div><span class="text-gray-500">الهاتف:</span> <span class="mr-2" dir="ltr">{{ visit.lead?.phone }}</span></div>
              <div><span class="text-gray-500">المهندس:</span> <span class="mr-2">{{ visit.engineer?.name }}</span></div>
              <div><span class="text-gray-500">الحالة:</span> <span :class="statusClass(visit.status)" class="badge mr-2">{{ statusLabel(visit.status) }}</span></div>
            </div>
            <div><span class="text-gray-500 text-sm">العنوان:</span><p class="mt-1">{{ visit.address }}</p></div>
            <div v-if="visit.client_requirements"><span class="text-gray-500 text-sm">متطلبات العميل:</span><p class="mt-1">{{ visit.client_requirements }}</p></div>
            <div v-if="visit.notes"><span class="text-gray-500 text-sm">ملاحظات:</span><p class="mt-1">{{ visit.notes }}</p></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">القياسات</h3>
            <button @click="showMeasurementModal = true" class="btn btn-sm btn-primary">إضافة قياس</button>
          </div>
          <div class="card-body">
            <div v-if="visit.measurements?.length === 0" class="text-center text-gray-500 py-8">لا توجد قياسات</div>
            <div v-else class="space-y-3">
              <div v-for="m in visit.measurements" :key="m.id" class="p-3 bg-gray-50 rounded-lg">
                <p class="font-medium">{{ m.room_name }}</p>
                <p class="text-sm text-gray-500">{{ m.length }}م × {{ m.width }}م × {{ m.height }}م = {{ m.area }} م²</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">الصور</h3>
            <label class="btn btn-sm btn-primary cursor-pointer">
              رفع صور
              <input type="file" multiple accept="image/*" @change="uploadPhotos" class="hidden" />
            </label>
          </div>
          <div class="card-body">
            <div v-if="visit.photos?.length === 0" class="text-center text-gray-500 py-8">لا توجد صور</div>
            <div v-else class="grid grid-cols-3 gap-4">
              <img v-for="photo in visit.photos" :key="photo.id" :src="`/storage/${photo.photo_path}`" class="rounded-lg object-cover h-32 w-full" />
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تغيير الحالة</h3></div>
          <div class="card-body space-y-3">
            <button v-if="visit.status === 'scheduled'" @click="updateStatus('in_progress')" class="btn btn-primary w-full">بدء المعاينة</button>
            <button v-if="visit.status === 'in_progress'" @click="updateStatus('completed')" class="btn btn-success w-full">إكمال المعاينة</button>
            <button v-if="visit.status !== 'cancelled' && visit.status !== 'completed'" @click="updateStatus('cancelled')" class="btn btn-danger w-full">إلغاء</button>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">إجراءات</h3></div>
          <div class="card-body space-y-3">
            <router-link :to="`/quotations/create?lead_id=${visit.lead_id}&site_visit_id=${visit.id}`" class="btn btn-secondary w-full">إنشاء عرض سعر</router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Measurement Modal -->
    <div v-if="showMeasurementModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة قياس</h3>
        <form @submit.prevent="addMeasurement" class="space-y-4">
          <div><label class="form-label">اسم الغرفة</label><input v-model="measurementForm.room_name" class="form-input" required /></div>
          <div class="grid grid-cols-3 gap-3">
            <div><label class="form-label">الطول (م)</label><input v-model="measurementForm.length" type="number" step="0.01" class="form-input" /></div>
            <div><label class="form-label">العرض (م)</label><input v-model="measurementForm.width" type="number" step="0.01" class="form-input" /></div>
            <div><label class="form-label">الارتفاع (م)</label><input v-model="measurementForm.height" type="number" step="0.01" class="form-input" /></div>
          </div>
          <div><label class="form-label">ملاحظات</label><textarea v-model="measurementForm.notes" class="form-input" rows="2"></textarea></div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showMeasurementModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const visit = ref(null);
const showMeasurementModal = ref(false);
const measurementForm = reactive({ room_name: '', length: null, width: null, height: null, notes: '' });

const fetchVisit = async () => {
  const { data } = await axios.get(`/site-visits/${route.params.id}`);
  visit.value = data.data;
};

const updateStatus = async (status) => {
  await axios.put(`/site-visits/${visit.value.id}`, { status });
  fetchVisit();
};

const addMeasurement = async () => {
  await axios.post(`/site-visits/${visit.value.id}/measurements`, measurementForm);
  showMeasurementModal.value = false;
  Object.assign(measurementForm, { room_name: '', length: null, width: null, height: null, notes: '' });
  fetchVisit();
};

const uploadPhotos = async (e) => {
  const formData = new FormData();
  for (const file of e.target.files) formData.append('photos[]', file);
  await axios.post(`/site-visits/${visit.value.id}/photos`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
  fetchVisit();
};

const formatDateTime = (d) => dayjs(d).format('YYYY/MM/DD HH:mm');
const statusLabel = (s) => ({ scheduled: 'مجدولة', in_progress: 'جارية', completed: 'مكتملة', cancelled: 'ملغاة' }[s] || s);
const statusClass = (s) => ({ scheduled: 'badge-info', in_progress: 'badge-warning', completed: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchVisit);
</script>
