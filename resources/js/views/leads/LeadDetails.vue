<template>
  <div v-if="lead" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">{{ lead.name }}</h2>
        <p class="text-gray-500">{{ lead.phone }}</p>
      </div>
      <div class="flex items-center gap-3">
        <router-link :to="`/leads/${lead.id}/edit`" class="btn btn-secondary">تعديل</router-link>
        <router-link :to="`/site-visits/create?lead_id=${lead.id}`" class="btn btn-primary">جدولة معاينة</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">بيانات العميل</h3></div>
          <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">البريد:</span> <span class="mr-2">{{ lead.email || '-' }}</span></div>
            <div><span class="text-gray-500">واتساب:</span> <span class="mr-2" dir="ltr">{{ lead.whatsapp || '-' }}</span></div>
            <div><span class="text-gray-500">المصدر:</span> <span class="mr-2">{{ sourceLabel(lead.source) }}</span></div>
            <div><span class="text-gray-500">الحالة:</span> <span :class="statusClass(lead.status)" class="badge mr-2">{{ statusLabel(lead.status) }}</span></div>
            <div><span class="text-gray-500">المرحلة:</span> <span class="badge badge-gray mr-2">{{ stageLabel(lead.stage) }}</span></div>
            <div><span class="text-gray-500">نوع المشروع:</span> <span class="mr-2">{{ lead.project_type || '-' }}</span></div>
            <div><span class="text-gray-500">الميزانية:</span> <span class="mr-2">{{ lead.estimated_budget ? formatCurrency(lead.estimated_budget) : '-' }}</span></div>
            <div><span class="text-gray-500">المسؤول:</span> <span class="mr-2">{{ lead.assigned_user?.name || '-' }}</span></div>
            <div class="col-span-2"><span class="text-gray-500">العنوان:</span> <span class="mr-2">{{ lead.address || '-' }}</span></div>
            <div class="col-span-2"><span class="text-gray-500">ملاحظات:</span> <span class="mr-2">{{ lead.notes || '-' }}</span></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">سجل النشاطات</h3>
            <button @click="showActivityModal = true" class="btn btn-sm btn-primary">إضافة نشاط</button>
          </div>
          <div class="card-body">
            <div v-if="lead.activities?.length === 0" class="text-center text-gray-500 py-8">لا توجد نشاطات</div>
            <div v-else class="space-y-4">
              <div v-for="activity in lead.activities" :key="activity.id" class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-900">{{ activity.description }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ activity.user?.name }} - {{ formatDateTime(activity.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تغيير الحالة</h3></div>
          <div class="card-body space-y-3">
            <select v-model="lead.status" @change="updateStatus" class="form-input">
              <option value="cold">بارد</option>
              <option value="warm">دافئ</option>
              <option value="hot">ساخن</option>
            </select>
            <select v-model="lead.stage" @change="updateStage" class="form-input">
              <option value="new">جديد</option>
              <option value="contacted">تم التواصل</option>
              <option value="qualified">مؤهل</option>
              <option value="proposal">عرض سعر</option>
              <option value="negotiation">تفاوض</option>
              <option value="won">مكسب</option>
              <option value="lost">خسارة</option>
            </select>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">المعاينات</h3></div>
          <div class="card-body">
            <div v-if="lead.site_visits?.length === 0" class="text-center text-gray-500 py-4">لا توجد معاينات</div>
            <div v-else class="space-y-2">
              <router-link v-for="visit in lead.site_visits" :key="visit.id" :to="`/site-visits/${visit.id}`" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                <p class="text-sm font-medium">{{ formatDate(visit.scheduled_at) }}</p>
                <p class="text-xs text-gray-500">م. {{ visit.engineer?.name }}</p>
              </router-link>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">عروض الأسعار</h3></div>
          <div class="card-body">
            <div v-if="lead.quotations?.length === 0" class="text-center text-gray-500 py-4">لا توجد عروض</div>
            <div v-else class="space-y-2">
              <router-link v-for="q in lead.quotations" :key="q.id" :to="`/quotations/${q.id}`" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                <p class="text-sm font-medium">{{ q.quotation_number }}</p>
                <p class="text-xs text-gray-500">{{ formatCurrency(q.total) }}</p>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Modal -->
    <div v-if="showActivityModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة نشاط</h3>
        <form @submit.prevent="addActivity" class="space-y-4">
          <div>
            <label class="form-label">النوع</label>
            <select v-model="activityForm.type" class="form-input">
              <option value="call">مكالمة</option>
              <option value="whatsapp">واتساب</option>
              <option value="email">بريد</option>
              <option value="meeting">اجتماع</option>
              <option value="note">ملاحظة</option>
            </select>
          </div>
          <div>
            <label class="form-label">الوصف</label>
            <textarea v-model="activityForm.description" class="form-input" rows="3" required></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showActivityModal = false" class="btn btn-secondary">إلغاء</button>
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
const lead = ref(null);
const showActivityModal = ref(false);
const activityForm = reactive({ type: 'note', description: '' });

const fetchLead = async () => {
  const { data } = await axios.get(`/leads/${route.params.id}`);
  lead.value = data.data;
};

const updateStatus = async () => {
  await axios.put(`/leads/${lead.value.id}`, { status: lead.value.status });
};

const updateStage = async () => {
  await axios.put(`/leads/${lead.value.id}`, { stage: lead.value.stage });
};

const addActivity = async () => {
  await axios.post(`/leads/${lead.value.id}/activities`, activityForm);
  showActivityModal.value = false;
  activityForm.description = '';
  fetchLead();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatDateTime = (d) => dayjs(d).format('YYYY/MM/DD HH:mm');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const sourceLabel = (s) => ({ whatsapp: 'واتساب', ads: 'إعلانات', call: 'مكالمات', website: 'موقع', referral: 'إحالة', other: 'أخرى' }[s] || s);
const statusLabel = (s) => ({ hot: 'ساخن', warm: 'دافئ', cold: 'بارد' }[s] || s);
const statusClass = (s) => ({ hot: 'badge-danger', warm: 'badge-warning', cold: 'badge-info' }[s] || 'badge-gray');
const stageLabel = (s) => ({ new: 'جديد', contacted: 'تم التواصل', qualified: 'مؤهل', proposal: 'عرض سعر', negotiation: 'تفاوض', won: 'مكسب', lost: 'خسارة' }[s] || s);

onMounted(fetchLead);
</script>
