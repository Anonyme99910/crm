<template>
  <div v-if="project" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">{{ project.project_number }}</p>
        <h2 class="text-2xl font-bold">{{ project.name }}</h2>
        <p class="text-gray-500">{{ project.lead?.name }}</p>
      </div>
      <span :class="statusClass(project.status)" class="badge text-base px-4 py-2">{{ statusLabel(project.status) }}</span>
    </div>

    <!-- Progress -->
    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between mb-2">
          <span class="font-medium">نسبة الإنجاز الكلية</span>
          <span class="text-2xl font-bold text-primary-600">{{ project.progress_percentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
          <div class="bg-primary-600 h-4 rounded-full transition-all" :style="{ width: project.progress_percentage + '%' }"></div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Phases -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">مراحل المشروع</h3>
            <button @click="showPhaseModal = true" class="btn btn-sm btn-primary">إضافة مرحلة</button>
          </div>
          <div class="card-body space-y-4">
            <div v-for="phase in project.phases" :key="phase.id" class="p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium">{{ phase.name }}</h4>
                <span :class="phaseStatusClass(phase.status)" class="badge">{{ phaseStatusLabel(phase.status) }}</span>
              </div>
              <div class="flex items-center gap-4 mb-2">
                <div class="flex-1">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" :style="{ width: phase.progress_percentage + '%' }"></div>
                  </div>
                </div>
                <span class="text-sm font-medium">{{ phase.progress_percentage }}%</span>
              </div>
              <div class="flex items-center gap-4">
                <input type="range" v-model="phase.progress_percentage" min="0" max="100" class="flex-1" @change="updatePhaseProgress(phase)" />
                <select v-model="phase.status" class="form-input w-32 text-sm" @change="updatePhaseStatus(phase)">
                  <option value="pending">قيد الانتظار</option>
                  <option value="in_progress">جاري</option>
                  <option value="completed">مكتمل</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Photos -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">صور المشروع</h3>
            <label class="btn btn-sm btn-primary cursor-pointer">
              رفع صور
              <input type="file" multiple accept="image/*" @change="uploadPhotos" class="hidden" />
            </label>
          </div>
          <div class="card-body">
            <div v-if="project.photos?.length === 0" class="text-center text-gray-500 py-8">لا توجد صور</div>
            <div v-else class="grid grid-cols-4 gap-4">
              <div v-for="photo in project.photos" :key="photo.id" class="relative group">
                <img :src="`/storage/${photo.photo_path}`" class="rounded-lg object-cover h-24 w-full" />
                <span class="absolute bottom-1 right-1 badge badge-gray text-xs">{{ photoTypeLabel(photo.type) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Updates -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">تحديثات المشروع</h3>
            <button @click="showUpdateModal = true" class="btn btn-sm btn-primary">إضافة تحديث</button>
          </div>
          <div class="card-body space-y-4">
            <div v-for="update in project.updates" :key="update.id" class="p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium">{{ update.title }}</h4>
                <span class="text-xs text-gray-500">{{ formatDateTime(update.created_at) }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ update.content }}</p>
              <p class="text-xs text-gray-400 mt-2">{{ update.user?.name }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">معلومات المشروع</h3></div>
          <div class="card-body space-y-3 text-sm">
            <div><span class="text-gray-500">مدير المشروع:</span> <span class="mr-2">{{ project.manager?.name }}</span></div>
            <div><span class="text-gray-500">قيمة العقد:</span> <span class="mr-2 font-medium">{{ formatCurrency(project.contract_value) }}</span></div>
            <div><span class="text-gray-500">تاريخ البدء:</span> <span class="mr-2">{{ formatDate(project.start_date) }}</span></div>
            <div><span class="text-gray-500">تاريخ الانتهاء:</span> <span class="mr-2">{{ formatDate(project.expected_end_date) }}</span></div>
            <div><span class="text-gray-500">العنوان:</span> <span class="mr-2">{{ project.address }}</span></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تغيير الحالة</h3></div>
          <div class="card-body">
            <select v-model="project.status" @change="updateProjectStatus" class="form-input">
              <option value="pending">قيد الانتظار</option>
              <option value="in_progress">جاري التنفيذ</option>
              <option value="on_hold">متوقف</option>
              <option value="completed">مكتمل</option>
            </select>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">روابط سريعة</h3></div>
          <div class="card-body space-y-2">
            <router-link :to="`/tasks?project_id=${project.id}`" class="btn btn-secondary w-full">المهام</router-link>
            <router-link :to="`/invoices?project_id=${project.id}`" class="btn btn-secondary w-full">الفواتير</router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Phase Modal -->
    <div v-if="showPhaseModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة مرحلة</h3>
        <form @submit.prevent="addPhase" class="space-y-4">
          <div><label class="form-label">اسم المرحلة</label><input v-model="phaseForm.name" class="form-input" required /></div>
          <div><label class="form-label">الميزانية</label><input v-model="phaseForm.budget" type="number" class="form-input" min="0" /></div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showPhaseModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Update Modal -->
    <div v-if="showUpdateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة تحديث</h3>
        <form @submit.prevent="addUpdate" class="space-y-4">
          <div><label class="form-label">العنوان</label><input v-model="updateForm.title" class="form-input" required /></div>
          <div><label class="form-label">المحتوى</label><textarea v-model="updateForm.content" class="form-input" rows="3" required></textarea></div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="updateForm.visible_to_client" id="visible" class="rounded" />
            <label for="visible" class="text-sm">مرئي للعميل</label>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showUpdateModal = false" class="btn btn-secondary">إلغاء</button>
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
const project = ref(null);
const showPhaseModal = ref(false);
const showUpdateModal = ref(false);
const phaseForm = reactive({ name: '', budget: 0 });
const updateForm = reactive({ title: '', content: '', visible_to_client: false });

const fetchProject = async () => {
  const { data } = await axios.get(`/projects/${route.params.id}`);
  project.value = data.data;
};

const updateProjectStatus = async () => {
  await axios.put(`/projects/${project.value.id}`, { status: project.value.status });
};

const updatePhaseProgress = async (phase) => {
  await axios.put(`/projects/${project.value.id}/phases/${phase.id}`, { progress_percentage: phase.progress_percentage });
  fetchProject();
};

const updatePhaseStatus = async (phase) => {
  await axios.put(`/projects/${project.value.id}/phases/${phase.id}`, { status: phase.status });
};

const addPhase = async () => {
  await axios.post(`/projects/${project.value.id}/phases`, phaseForm);
  showPhaseModal.value = false;
  Object.assign(phaseForm, { name: '', budget: 0 });
  fetchProject();
};

const addUpdate = async () => {
  await axios.post(`/projects/${project.value.id}/updates`, updateForm);
  showUpdateModal.value = false;
  Object.assign(updateForm, { title: '', content: '', visible_to_client: false });
  fetchProject();
};

const uploadPhotos = async (e) => {
  const formData = new FormData();
  for (const file of e.target.files) formData.append('photos[]', file);
  formData.append('type', 'during');
  await axios.post(`/projects/${project.value.id}/photos`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
  fetchProject();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatDateTime = (d) => dayjs(d).format('YYYY/MM/DD HH:mm');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري التنفيذ', on_hold: 'متوقف', completed: 'مكتمل' }[s] || s);
const statusClass = (s) => ({ pending: 'badge-gray', in_progress: 'badge-info', on_hold: 'badge-warning', completed: 'badge-success' }[s] || 'badge-gray');
const phaseStatusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري', completed: 'مكتمل' }[s] || s);
const phaseStatusClass = (s) => ({ pending: 'badge-gray', in_progress: 'badge-info', completed: 'badge-success' }[s] || 'badge-gray');
const photoTypeLabel = (t) => ({ before: 'قبل', during: 'أثناء', after: 'بعد' }[t] || t);

onMounted(fetchProject);
</script>
