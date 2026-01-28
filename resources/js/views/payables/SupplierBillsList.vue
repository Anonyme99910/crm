<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">فواتير الموردين</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        فاتورة جديدة
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">إجمالي المستحق</div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalPayable) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">مستحق الدفع</div>
        <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(overdueAmount) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">فواتير معلقة</div>
        <div class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">تحتاج مطابقة</div>
        <div class="text-2xl font-bold text-blue-600">{{ unmatchedCount }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">المورد</label>
          <select v-model="filters.supplier_id" @change="loadBills" class="input-field">
            <option value="">الكل</option>
            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
          <select v-model="filters.status" @change="loadBills" class="input-field">
            <option value="">الكل</option>
            <option value="draft">مسودة</option>
            <option value="pending_approval">بانتظار الاعتماد</option>
            <option value="approved">معتمد</option>
            <option value="partially_paid">مدفوع جزئياً</option>
            <option value="paid">مدفوع</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">حالة المطابقة</label>
          <select v-model="filters.matching_status" @change="loadBills" class="input-field">
            <option value="">الكل</option>
            <option value="unmatched">غير مطابق</option>
            <option value="partial_match">مطابقة جزئية</option>
            <option value="matched">مطابق</option>
            <option value="discrepancy">يوجد فرق</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
          <input v-model="filters.from_date" type="date" @change="loadBills" class="input-field">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
          <input v-model="filters.to_date" type="date" @change="loadBills" class="input-field">
        </div>
      </div>
    </div>

    <!-- Bills List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الفاتورة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المورد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الاستحقاق</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المطابقة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="bill in bills" :key="bill.id" class="hover:bg-gray-50" :class="{ 'bg-red-50': isOverdue(bill) }">
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ bill.bill_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ bill.supplier?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ formatDate(bill.bill_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap" :class="isOverdue(bill) ? 'text-red-600 font-bold' : 'text-gray-500'">
                {{ formatDate(bill.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap font-bold">{{ formatCurrency(bill.total_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap" :class="bill.balance > 0 ? 'text-red-600 font-bold' : 'text-green-600'">
                {{ formatCurrency(bill.balance) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getMatchingClass(bill.matching_status)">
                  {{ getMatchingLabel(bill.matching_status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(bill.status)">
                  {{ getStatusLabel(bill.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="viewBill(bill)" class="text-blue-600 hover:text-blue-800">عرض</button>
                  <button v-if="bill.status === 'draft'" @click="approveBill(bill)" class="text-green-600 hover:text-green-800">اعتماد</button>
                  <button v-if="['approved', 'partially_paid'].includes(bill.status)" @click="openPayModal(bill)" class="text-purple-600 hover:text-purple-800">دفع</button>
                  <button v-if="!bill.goods_received" @click="markReceived(bill)" class="text-orange-600 hover:text-orange-800">استلام</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Bill Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">فاتورة مورد جديدة</h2>
        </div>
        <form @submit.prevent="createBill" class="p-6 space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">المورد</label>
              <select v-model="form.supplier_id" required class="input-field">
                <option value="">اختر المورد</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">رقم فاتورة المورد</label>
              <input v-model="form.supplier_invoice_number" type="text" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">أمر الشراء</label>
              <select v-model="form.purchase_order_id" class="input-field">
                <option value="">بدون</option>
                <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">{{ po.po_number }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الفاتورة</label>
              <input v-model="form.bill_date" type="date" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
              <input v-model="form.due_date" type="date" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">المشروع</label>
              <select v-model="form.project_id" class="input-field">
                <option value="">بدون</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
          </div>

          <!-- Items -->
          <div class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold">بنود الفاتورة</h3>
              <button type="button" @click="addItem" class="text-blue-600 hover:text-blue-800 text-sm">+ إضافة بند</button>
            </div>
            <table class="min-w-full">
              <thead>
                <tr class="text-xs text-gray-500">
                  <th class="text-right pb-2">الوصف</th>
                  <th class="text-right pb-2">الكمية</th>
                  <th class="text-right pb-2">السعر</th>
                  <th class="text-right pb-2">الضريبة %</th>
                  <th class="text-right pb-2">الإجمالي</th>
                  <th class="pb-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="index" class="border-t">
                  <td class="py-2 pl-2">
                    <input v-model="item.description" type="text" required class="input-field text-sm">
                  </td>
                  <td class="py-2 px-2 w-24">
                    <input v-model.number="item.quantity" type="number" step="0.01" min="0.01" required class="input-field text-sm" dir="ltr">
                  </td>
                  <td class="py-2 px-2 w-28">
                    <input v-model.number="item.unit_price" type="number" step="0.01" min="0" required class="input-field text-sm" dir="ltr">
                  </td>
                  <td class="py-2 px-2 w-20">
                    <input v-model.number="item.tax_rate" type="number" step="0.01" min="0" max="100" class="input-field text-sm" dir="ltr">
                  </td>
                  <td class="py-2 px-2 w-28 font-mono text-sm">{{ formatCurrency(getItemTotal(item)) }}</td>
                  <td class="py-2">
                    <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">×</button>
                  </td>
                </tr>
              </tbody>
              <tfoot class="border-t font-bold">
                <tr>
                  <td colspan="4" class="py-2 text-left">الإجمالي</td>
                  <td class="py-2 px-2 font-mono">{{ formatCurrency(billTotal) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea v-model="form.notes" rows="2" class="input-field"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Pay Modal -->
    <div v-if="showPayModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">دفع فاتورة</h2>
        </div>
        <form @submit.prevent="payBill" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">الرصيد المستحق: <span class="font-bold text-red-600">{{ formatCurrency(selectedBill?.balance) }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
            <input v-model.number="payForm.amount" type="number" step="0.01" min="0.01" :max="selectedBill?.balance" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحساب البنكي</label>
            <select v-model="payForm.bank_account_id" required class="input-field">
              <option value="">اختر الحساب</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                {{ account.account_name }} ({{ formatCurrency(account.current_balance) }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الدفع</label>
            <input v-model="payForm.payment_date" type="date" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
            <select v-model="payForm.payment_method" required class="input-field">
              <option value="bank_transfer">تحويل بنكي</option>
              <option value="check">شيك</option>
              <option value="cash">نقدي</option>
            </select>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showPayModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الدفع...' : 'دفع' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/utils/axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const bills = ref([]);
const suppliers = ref([]);
const projects = ref([]);
const purchaseOrders = ref([]);
const bankAccounts = ref([]);
const selectedBill = ref(null);
const saving = ref(false);

const showCreateModal = ref(false);
const showPayModal = ref(false);

const filters = ref({ supplier_id: '', status: '', matching_status: '', from_date: '', to_date: '' });

const form = ref({
  supplier_id: '', purchase_order_id: '', project_id: '', supplier_invoice_number: '',
  bill_date: new Date().toISOString().split('T')[0], due_date: '', notes: '',
  items: [{ description: '', quantity: 1, unit_price: 0, tax_rate: 15 }],
});

const payForm = ref({ amount: 0, bank_account_id: '', payment_date: new Date().toISOString().split('T')[0], payment_method: 'bank_transfer' });

const totalPayable = computed(() => bills.value.reduce((sum, b) => sum + parseFloat(b.balance || 0), 0));
const overdueAmount = computed(() => bills.value.filter(b => isOverdue(b)).reduce((sum, b) => sum + parseFloat(b.balance || 0), 0));
const pendingCount = computed(() => bills.value.filter(b => b.status === 'pending_approval').length);
const unmatchedCount = computed(() => bills.value.filter(b => b.matching_status === 'unmatched').length);

const billTotal = computed(() => form.value.items.reduce((sum, item) => sum + getItemTotal(item), 0));

const getItemTotal = (item) => {
  const subtotal = (item.quantity || 0) * (item.unit_price || 0);
  const tax = subtotal * ((item.tax_rate || 0) / 100);
  return subtotal + tax;
};

const loadBills = async () => {
  try {
    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([key, value]) => { if (value) params.append(key, value); });
    const response = await axios.get(`/api/payables/bills?${params}`);
    bills.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading bills:', error);
  }
};

const loadData = async () => {
  try {
    const [suppliersRes, projectsRes, poRes, accountsRes] = await Promise.all([
      axios.get('/api/suppliers'),
      axios.get('/api/projects'),
      axios.get('/api/purchase-orders'),
      axios.get('/api/treasury/bank-accounts'),
    ]);
    suppliers.value = suppliersRes.data;
    projects.value = projectsRes.data;
    purchaseOrders.value = poRes.data.data || poRes.data;
    bankAccounts.value = accountsRes.data;
  } catch (error) {
    console.error('Error loading data:', error);
  }
};

const addItem = () => form.value.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 15 });
const removeItem = (index) => form.value.items.splice(index, 1);

const createBill = async () => {
  saving.value = true;
  try {
    await axios.post('/api/payables/bills', form.value);
    showCreateModal.value = false;
    resetForm();
    await loadBills();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const approveBill = async (bill) => {
  if (!confirm('هل أنت متأكد من اعتماد هذه الفاتورة؟')) return;
  try {
    await axios.post(`/api/payables/bills/${bill.id}/approve`);
    await loadBills();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const markReceived = async (bill) => {
  try {
    await axios.post(`/api/payables/bills/${bill.id}/goods-received`, { goods_received_date: new Date().toISOString().split('T')[0] });
    await loadBills();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const openPayModal = (bill) => {
  selectedBill.value = bill;
  payForm.value = { amount: bill.balance, bank_account_id: '', payment_date: new Date().toISOString().split('T')[0], payment_method: 'bank_transfer' };
  showPayModal.value = true;
};

const payBill = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/payables/bills/${selectedBill.value.id}/pay`, payForm.value);
    showPayModal.value = false;
    await loadBills();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewBill = (bill) => router.push(`/dashboard/payables/bills/${bill.id}`);

const resetForm = () => {
  form.value = {
    supplier_id: '', purchase_order_id: '', project_id: '', supplier_invoice_number: '',
    bill_date: new Date().toISOString().split('T')[0], due_date: '', notes: '',
    items: [{ description: '', quantity: 1, unit_price: 0, tax_rate: 15 }],
  };
};

const isOverdue = (bill) => bill.balance > 0 && new Date(bill.due_date) < new Date();

const formatCurrency = (amount) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount || 0);
const formatDate = (date) => date ? new Date(date).toLocaleDateString('ar-EG') : '';

const getStatusLabel = (status) => {
  const labels = { draft: 'مسودة', pending_approval: 'بانتظار الاعتماد', approved: 'معتمد', partially_paid: 'مدفوع جزئياً', paid: 'مدفوع', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { draft: 'bg-gray-100 text-gray-800', pending_approval: 'bg-yellow-100 text-yellow-800', approved: 'bg-blue-100 text-blue-800', partially_paid: 'bg-orange-100 text-orange-800', paid: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getMatchingLabel = (status) => {
  const labels = { unmatched: 'غير مطابق', partial_match: 'مطابقة جزئية', matched: 'مطابق', discrepancy: 'يوجد فرق' };
  return labels[status] || status;
};

const getMatchingClass = (status) => {
  const classes = { unmatched: 'bg-gray-100 text-gray-800', partial_match: 'bg-yellow-100 text-yellow-800', matched: 'bg-green-100 text-green-800', discrepancy: 'bg-red-100 text-red-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadBills();
  loadData();
});
</script>
