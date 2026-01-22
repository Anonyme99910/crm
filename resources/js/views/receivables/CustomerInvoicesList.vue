<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">فواتير العملاء</h1>
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
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.total_receivable) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">متأخر السداد</div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.overdue_amount) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">فواتير معلقة</div>
        <div class="text-2xl font-bold text-yellow-600">{{ summary.pending_invoices }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">تحصيلات الشهر</div>
        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(summary.this_month_collected) }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
          <select v-model="filters.client_id" @change="loadInvoices" class="input-field">
            <option value="">الكل</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">نوع الفاتورة</label>
          <select v-model="filters.invoice_type" @change="loadInvoices" class="input-field">
            <option value="">الكل</option>
            <option value="progress">مستخلص</option>
            <option value="final">ختامية</option>
            <option value="retention">ضمان</option>
            <option value="variation">تغييرات</option>
            <option value="advance">دفعة مقدمة</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
          <select v-model="filters.status" @change="loadInvoices" class="input-field">
            <option value="">الكل</option>
            <option value="draft">مسودة</option>
            <option value="approved">معتمدة</option>
            <option value="sent">مرسلة</option>
            <option value="partially_paid">مدفوعة جزئياً</option>
            <option value="paid">مدفوعة</option>
            <option value="overdue">متأخرة</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
          <input v-model="filters.from_date" type="date" @change="loadInvoices" class="input-field">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
          <input v-model="filters.to_date" type="date" @change="loadInvoices" class="input-field">
        </div>
      </div>
    </div>

    <!-- Invoices List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الفاتورة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العميل</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المشروع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50" :class="{ 'bg-red-50': isOverdue(invoice) }">
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ invoice.invoice_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ invoice.client?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ invoice.project?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(invoice.invoice_type)">
                  {{ getTypeLabel(invoice.invoice_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ formatDate(invoice.invoice_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-bold">{{ formatCurrency(invoice.total_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap" :class="invoice.balance > 0 ? 'text-green-600 font-bold' : 'text-gray-500'">
                {{ formatCurrency(invoice.balance) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(invoice.status)">
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="viewInvoice(invoice)" class="text-blue-600 hover:text-blue-800">عرض</button>
                  <button v-if="invoice.status === 'draft'" @click="approveInvoice(invoice)" class="text-green-600 hover:text-green-800">اعتماد</button>
                  <button v-if="invoice.status === 'approved'" @click="sendInvoice(invoice)" class="text-purple-600 hover:text-purple-800">إرسال</button>
                  <button v-if="['sent', 'partially_paid', 'overdue'].includes(invoice.status)" @click="openPaymentModal(invoice)" class="text-orange-600 hover:text-orange-800">تحصيل</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Invoice Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">فاتورة عميل جديدة</h2>
        </div>
        <form @submit.prevent="createInvoice" class="p-6 space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">نوع الفاتورة</label>
              <select v-model="form.invoice_type" required class="input-field">
                <option value="progress">مستخلص</option>
                <option value="final">ختامية</option>
                <option value="retention">ضمان</option>
                <option value="variation">تغييرات</option>
                <option value="advance">دفعة مقدمة</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
              <select v-model="form.client_id" required class="input-field">
                <option value="">اختر العميل</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">المشروع</label>
              <select v-model="form.project_id" required class="input-field">
                <option value="">اختر المشروع</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الفاتورة</label>
              <input v-model="form.invoice_date" type="date" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
              <input v-model="form.due_date" type="date" required class="input-field">
            </div>
            <div v-if="form.invoice_type === 'progress'">
              <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الإنجاز %</label>
              <input v-model.number="form.completion_percentage" type="number" step="0.01" min="0" max="100" class="input-field" dir="ltr">
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
                  <th class="text-right pb-2">الوحدة</th>
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
                  <td class="py-2 px-2 w-20">
                    <input v-model="item.unit" type="text" class="input-field text-sm">
                  </td>
                  <td class="py-2 px-2 w-24">
                    <input v-model.number="item.quantity" type="number" step="0.0001" min="0.0001" required class="input-field text-sm" dir="ltr">
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
            </table>
          </div>

          <!-- Totals -->
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">خصم الضمان</label>
              <input v-model.number="form.retention_amount" type="number" step="0.01" min="0" class="input-field" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">خصم</label>
              <input v-model.number="form.discount_amount" type="number" step="0.01" min="0" class="input-field" dir="ltr">
            </div>
            <div class="flex items-end">
              <div class="bg-blue-50 p-3 rounded-lg w-full text-center">
                <span class="text-sm text-gray-600">الإجمالي: </span>
                <span class="font-bold text-blue-600">{{ formatCurrency(invoiceTotal) }}</span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <textarea v-model="form.description" rows="2" class="input-field"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">تسجيل تحصيل</h2>
        </div>
        <form @submit.prevent="recordPayment" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">الرصيد المستحق: <span class="font-bold text-green-600">{{ formatCurrency(selectedInvoice?.balance) }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
            <input v-model.number="paymentForm.amount" type="number" step="0.01" min="0.01" :max="selectedInvoice?.balance" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحساب البنكي</label>
            <select v-model="paymentForm.bank_account_id" required class="input-field">
              <option value="">اختر الحساب</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.account_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التحصيل</label>
            <input v-model="paymentForm.payment_date" type="date" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
            <select v-model="paymentForm.payment_method" required class="input-field">
              <option value="bank_transfer">تحويل بنكي</option>
              <option value="check">شيك</option>
              <option value="cash">نقدي</option>
              <option value="credit_card">بطاقة ائتمان</option>
            </select>
          </div>
          <div v-if="paymentForm.payment_method === 'check'" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">رقم الشيك</label>
              <input v-model="paymentForm.check_number" type="text" class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الشيك</label>
              <input v-model="paymentForm.check_date" type="date" class="input-field">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع</label>
            <input v-model="paymentForm.reference_number" type="text" class="input-field">
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showPaymentModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'تسجيل' }}</button>
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
const invoices = ref([]);
const clients = ref([]);
const projects = ref([]);
const bankAccounts = ref([]);
const summary = ref({ total_receivable: 0, overdue_amount: 0, pending_invoices: 0, this_month_collected: 0 });
const selectedInvoice = ref(null);
const saving = ref(false);

const showCreateModal = ref(false);
const showPaymentModal = ref(false);

const filters = ref({ client_id: '', invoice_type: '', status: '', from_date: '', to_date: '' });

const form = ref({
  invoice_type: 'progress', client_id: '', project_id: '', invoice_date: new Date().toISOString().split('T')[0],
  due_date: '', completion_percentage: null, retention_amount: 0, discount_amount: 0, description: '',
  items: [{ description: '', unit: '', quantity: 1, unit_price: 0, tax_rate: 15 }],
});

const paymentForm = ref({ amount: 0, bank_account_id: '', payment_date: new Date().toISOString().split('T')[0], payment_method: 'bank_transfer', reference_number: '', check_number: '', check_date: '' });

const getItemTotal = (item) => {
  const subtotal = (item.quantity || 0) * (item.unit_price || 0);
  const tax = subtotal * ((item.tax_rate || 0) / 100);
  return subtotal + tax;
};

const invoiceTotal = computed(() => {
  const itemsTotal = form.value.items.reduce((sum, item) => sum + getItemTotal(item), 0);
  return itemsTotal - (form.value.retention_amount || 0) - (form.value.discount_amount || 0);
});

const loadInvoices = async () => {
  try {
    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([key, value]) => { if (value) params.append(key, value); });
    const [invoicesRes, summaryRes] = await Promise.all([
      axios.get(`/api/receivables/invoices?${params}`),
      axios.get('/api/receivables/invoices/summary'),
    ]);
    invoices.value = invoicesRes.data.data || invoicesRes.data;
    summary.value = summaryRes.data;
  } catch (error) {
    console.error('Error loading invoices:', error);
  }
};

const loadData = async () => {
  try {
    const [clientsRes, projectsRes, accountsRes] = await Promise.all([
      axios.get('/api/leads?type=client'),
      axios.get('/api/projects'),
      axios.get('/api/treasury/bank-accounts'),
    ]);
    clients.value = clientsRes.data.data || clientsRes.data;
    projects.value = projectsRes.data.data || projectsRes.data;
    bankAccounts.value = accountsRes.data;
  } catch (error) {
    console.error('Error loading data:', error);
  }
};

const addItem = () => form.value.items.push({ description: '', unit: '', quantity: 1, unit_price: 0, tax_rate: 15 });
const removeItem = (index) => form.value.items.splice(index, 1);

const createInvoice = async () => {
  saving.value = true;
  try {
    await axios.post('/api/receivables/invoices', form.value);
    showCreateModal.value = false;
    resetForm();
    await loadInvoices();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const approveInvoice = async (invoice) => {
  if (!confirm('هل أنت متأكد من اعتماد هذه الفاتورة؟')) return;
  try {
    await axios.post(`/api/receivables/invoices/${invoice.id}/approve`);
    await loadInvoices();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const sendInvoice = async (invoice) => {
  if (!confirm('هل أنت متأكد من إرسال هذه الفاتورة للعميل؟')) return;
  try {
    await axios.post(`/api/receivables/invoices/${invoice.id}/send`);
    await loadInvoices();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const openPaymentModal = (invoice) => {
  selectedInvoice.value = invoice;
  paymentForm.value = { amount: invoice.balance, bank_account_id: '', payment_date: new Date().toISOString().split('T')[0], payment_method: 'bank_transfer', reference_number: '', check_number: '', check_date: '' };
  showPaymentModal.value = true;
};

const recordPayment = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/receivables/invoices/${selectedInvoice.value.id}/payment`, paymentForm.value);
    showPaymentModal.value = false;
    await loadInvoices();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewInvoice = (invoice) => router.push(`/dashboard/receivables/invoices/${invoice.id}`);

const resetForm = () => {
  form.value = {
    invoice_type: 'progress', client_id: '', project_id: '', invoice_date: new Date().toISOString().split('T')[0],
    due_date: '', completion_percentage: null, retention_amount: 0, discount_amount: 0, description: '',
    items: [{ description: '', unit: '', quantity: 1, unit_price: 0, tax_rate: 15 }],
  };
};

const isOverdue = (invoice) => invoice.balance > 0 && new Date(invoice.due_date) < new Date();

const formatCurrency = (amount) => new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(amount || 0);
const formatDate = (date) => date ? new Date(date).toLocaleDateString('ar-SA') : '';

const getTypeLabel = (type) => {
  const labels = { progress: 'مستخلص', final: 'ختامية', retention: 'ضمان', variation: 'تغييرات', advance: 'دفعة مقدمة' };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = { progress: 'bg-blue-100 text-blue-800', final: 'bg-green-100 text-green-800', retention: 'bg-purple-100 text-purple-800', variation: 'bg-orange-100 text-orange-800', advance: 'bg-yellow-100 text-yellow-800' };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
  const labels = { draft: 'مسودة', pending_approval: 'بانتظار الاعتماد', approved: 'معتمدة', sent: 'مرسلة', partially_paid: 'مدفوعة جزئياً', paid: 'مدفوعة', overdue: 'متأخرة', cancelled: 'ملغاة' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { draft: 'bg-gray-100 text-gray-800', pending_approval: 'bg-yellow-100 text-yellow-800', approved: 'bg-blue-100 text-blue-800', sent: 'bg-purple-100 text-purple-800', partially_paid: 'bg-orange-100 text-orange-800', paid: 'bg-green-100 text-green-800', overdue: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadInvoices();
  loadData();
});
</script>
