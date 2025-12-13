<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold">أوامر الشراء</h2>
      <button @click="showCreateModal = true" class="btn btn-primary">إنشاء أمر شراء</button>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>رقم الأمر</th><th>المورد</th><th>المشروع</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th></tr>
          </thead>
          <tbody>
            <tr v-for="po in orders" :key="po.id">
              <td class="font-medium">{{ po.po_number }}</td>
              <td>{{ po.supplier?.name }}</td>
              <td>{{ po.project?.name || '-' }}</td>
              <td>{{ formatCurrency(po.total) }}</td>
              <td><span :class="statusClass(po.status)" class="badge">{{ statusLabel(po.status) }}</span></td>
              <td>{{ formatDate(po.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto py-8">
      <div class="bg-white rounded-xl p-6 w-full max-w-2xl">
        <h3 class="text-lg font-semibold mb-4">إنشاء أمر شراء</h3>
        <form @submit.prevent="createOrder" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="form-label">المورد *</label>
              <select v-model="form.supplier_id" class="form-input" required>
                <option :value="null">-- اختر --</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">المشروع</label>
              <select v-model="form.project_id" class="form-input">
                <option :value="null">-- اختر --</option>
                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="form-label">البنود</label>
              <button type="button" @click="addItem" class="text-sm text-primary-600">+ إضافة</button>
            </div>
            <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 mb-2">
              <select v-model="item.material_id" class="form-input col-span-5">
                <option :value="null">-- الخامة --</option>
                <option v-for="m in materials" :key="m.id" :value="m.id">{{ m.name }}</option>
              </select>
              <input v-model="item.quantity" type="number" class="form-input col-span-3" placeholder="الكمية" min="1" />
              <input v-model="item.unit_price" type="number" class="form-input col-span-3" placeholder="السعر" min="0" />
              <button type="button" @click="form.items.splice(i, 1)" class="text-red-600 col-span-1">×</button>
            </div>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showCreateModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const orders = ref([]);
const suppliers = ref([]);
const projects = ref([]);
const materials = ref([]);
const showCreateModal = ref(false);
const form = reactive({ supplier_id: null, project_id: null, items: [{ material_id: null, quantity: 1, unit_price: 0 }] });

const fetchData = async () => {
  const [ordersRes, suppliersRes, projectsRes, materialsRes] = await Promise.all([
    axios.get('/purchase-orders'),
    axios.get('/suppliers'),
    axios.get('/projects'),
    axios.get('/materials')
  ]);
  orders.value = ordersRes.data.data;
  suppliers.value = suppliersRes.data.data;
  projects.value = projectsRes.data.data;
  materials.value = materialsRes.data.data;
};

const addItem = () => form.items.push({ material_id: null, quantity: 1, unit_price: 0 });

const createOrder = async () => {
  await axios.post('/purchase-orders', form);
  showCreateModal.value = false;
  form.items = [{ material_id: null, quantity: 1, unit_price: 0 }];
  fetchData();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ draft: 'مسودة', pending_approval: 'بانتظار الموافقة', approved: 'موافق عليه', sent: 'مرسل', received: 'مستلم', cancelled: 'ملغي' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', approved: 'badge-info', sent: 'badge-warning', received: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchData);
</script>
