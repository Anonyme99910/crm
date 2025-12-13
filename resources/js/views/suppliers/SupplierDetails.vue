<template>
  <div v-if="supplier" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">{{ supplier.code }}</p>
        <h2 class="text-2xl font-bold">{{ supplier.name }}</h2>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-yellow-500 text-2xl">★</span>
        <span class="text-2xl font-bold">{{ supplier.rating || 0 }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">البيانات</h3></div>
          <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">النوع:</span> <span class="mr-2">{{ typeLabel(supplier.type) }}</span></div>
            <div><span class="text-gray-500">الهاتف:</span> <span class="mr-2" dir="ltr">{{ supplier.phone }}</span></div>
            <div><span class="text-gray-500">البريد:</span> <span class="mr-2">{{ supplier.email || '-' }}</span></div>
            <div><span class="text-gray-500">جهة الاتصال:</span> <span class="mr-2">{{ supplier.contact_person || '-' }}</span></div>
            <div><span class="text-gray-500">التخصص:</span> <span class="mr-2">{{ supplier.specialization || '-' }}</span></div>
            <div class="col-span-2"><span class="text-gray-500">العنوان:</span> <span class="mr-2">{{ supplier.address || '-' }}</span></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">أوامر الشراء</h3></div>
          <div class="card-body">
            <div v-if="supplier.purchase_orders?.length === 0" class="text-center text-gray-500 py-4">لا توجد أوامر شراء</div>
            <div v-else class="space-y-2">
              <div v-for="po in supplier.purchase_orders" :key="po.id" class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                <div>
                  <p class="font-medium">{{ po.po_number }}</p>
                  <p class="text-sm text-gray-500">{{ formatCurrency(po.total) }}</p>
                </div>
                <span :class="poStatusClass(po.status)" class="badge">{{ poStatusLabel(po.status) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">إضافة تقييم</h3></div>
          <div class="card-body space-y-4">
            <div v-for="(label, key) in ratingLabels" :key="key">
              <label class="form-label text-sm">{{ label }}</label>
              <div class="flex gap-1">
                <button v-for="n in 5" :key="n" type="button" @click="ratingForm[key] = n" :class="n <= ratingForm[key] ? 'text-yellow-500' : 'text-gray-300'" class="text-xl">★</button>
              </div>
            </div>
            <textarea v-model="ratingForm.comments" class="form-input" rows="2" placeholder="تعليقات"></textarea>
            <button @click="submitRating" class="btn btn-primary w-full">حفظ التقييم</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const supplier = ref(null);
const ratingLabels = { quality_rating: 'الجودة', delivery_rating: 'التسليم', price_rating: 'السعر', communication_rating: 'التواصل' };
const ratingForm = reactive({ quality_rating: 0, delivery_rating: 0, price_rating: 0, communication_rating: 0, comments: '' });

const fetchSupplier = async () => {
  const { data } = await axios.get(`/suppliers/${route.params.id}`);
  supplier.value = data.data;
};

const submitRating = async () => {
  await axios.post(`/suppliers/${supplier.value.id}/ratings`, ratingForm);
  Object.assign(ratingForm, { quality_rating: 0, delivery_rating: 0, price_rating: 0, communication_rating: 0, comments: '' });
  fetchSupplier();
};

const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const typeLabel = (t) => ({ supplier: 'مورد', contractor: 'مقاول', both: 'مورد ومقاول' }[t] || t);
const poStatusLabel = (s) => ({ draft: 'مسودة', pending_approval: 'بانتظار الموافقة', approved: 'موافق عليه', sent: 'مرسل', received: 'مستلم' }[s] || s);
const poStatusClass = (s) => ({ draft: 'badge-gray', approved: 'badge-info', received: 'badge-success' }[s] || 'badge-gray');

onMounted(fetchSupplier);
</script>
