<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">طلبات الاعتماد</h1>
      <div class="flex gap-2">
        <button @click="activeTab = 'pending'" :class="[activeTab === 'pending' ? 'btn-primary' : 'btn-secondary']">
          بانتظار الاعتماد ({{ stats.pending_count }})
        </button>
        <button @click="activeTab = 'my'" :class="[activeTab === 'my' ? 'btn-primary' : 'btn-secondary']">
          طلباتي
        </button>
        <button @click="showWorkflowsModal = true" class="btn-secondary">
          إدارة سير العمل
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">بانتظار الاعتماد</div>
        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_count }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">معتمد اليوم</div>
        <div class="text-2xl font-bold text-green-600">{{ stats.approved_today }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">مرفوض اليوم</div>
        <div class="text-2xl font-bold text-red-600">{{ stats.rejected_today }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">متوسط وقت الاعتماد</div>
        <div class="text-2xl font-bold text-blue-600">{{ stats.avg_approval_time?.toFixed(1) || 0 }} ساعة</div>
      </div>
    </div>

    <!-- Requests List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المرجع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">مقدم الطلب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستوى</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(request.approvable_type)">
                  {{ getTypeLabel(request.approvable_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                {{ request.approvable?.reference_number || request.approvable?.invoice_number || request.approvable?.fund_number || '#' + request.approvable_id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap font-bold">{{ formatCurrency(request.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ request.requested_by_user?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ formatDate(request.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm">{{ request.current_level }} / {{ request.workflow?.approval_levels?.length }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(request.status)">
                  {{ getStatusLabel(request.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div v-if="request.status === 'pending' && activeTab === 'pending'" class="flex gap-2">
                  <button @click="openApproveModal(request)" class="text-green-600 hover:text-green-800">اعتماد</button>
                  <button @click="openRejectModal(request)" class="text-red-600 hover:text-red-800">رفض</button>
                </div>
                <button v-else @click="viewRequest(request)" class="text-blue-600 hover:text-blue-800">عرض</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold text-green-600">اعتماد الطلب</h2>
        </div>
        <form @submit.prevent="approveRequest" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm"><strong>النوع:</strong> {{ getTypeLabel(selectedRequest?.approvable_type) }}</p>
            <p class="text-sm"><strong>المبلغ:</strong> {{ formatCurrency(selectedRequest?.amount) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات (اختياري)</label>
            <textarea v-model="actionForm.comments" rows="3" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showApproveModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700" :disabled="saving">
              {{ saving ? 'جاري الاعتماد...' : 'اعتماد' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold text-red-600">رفض الطلب</h2>
        </div>
        <form @submit.prevent="rejectRequest" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm"><strong>النوع:</strong> {{ getTypeLabel(selectedRequest?.approvable_type) }}</p>
            <p class="text-sm"><strong>المبلغ:</strong> {{ formatCurrency(selectedRequest?.amount) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">سبب الرفض <span class="text-red-500">*</span></label>
            <textarea v-model="actionForm.comments" rows="3" required class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showRejectModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary bg-red-600 hover:bg-red-700" :disabled="saving">
              {{ saving ? 'جاري الرفض...' : 'رفض' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Workflows Modal -->
    <div v-if="showWorkflowsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex justify-between items-center">
          <h2 class="text-xl font-bold">إدارة سير العمل</h2>
          <button @click="showCreateWorkflow = true" class="btn-primary text-sm">+ إضافة سير عمل</button>
        </div>
        <div class="p-6">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الاسم</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">النوع</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">نطاق المبلغ</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">المستويات</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الحالة</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="workflow in workflows" :key="workflow.id" class="border-t">
                <td class="px-4 py-2">{{ workflow.name }}</td>
                <td class="px-4 py-2">{{ getTypeLabel(workflow.model_type) }}</td>
                <td class="px-4 py-2 font-mono text-sm">
                  {{ formatCurrency(workflow.min_amount) }} - {{ workflow.max_amount ? formatCurrency(workflow.max_amount) : '∞' }}
                </td>
                <td class="px-4 py-2">{{ workflow.approval_levels?.length || 0 }}</td>
                <td class="px-4 py-2">
                  <span :class="workflow.is_active ? 'text-green-600' : 'text-gray-400'">
                    {{ workflow.is_active ? 'نشط' : 'غير نشط' }}
                  </span>
                </td>
                <td class="px-4 py-2">
                  <button @click="editWorkflow(workflow)" class="text-blue-600 hover:text-blue-800 ml-2">تعديل</button>
                  <button @click="deleteWorkflow(workflow)" class="text-red-600 hover:text-red-800">حذف</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-6 border-t flex justify-end">
          <button @click="showWorkflowsModal = false" class="btn-secondary">إغلاق</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Workflow Modal -->
    <div v-if="showCreateWorkflow" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">{{ editingWorkflow ? 'تعديل سير العمل' : 'إضافة سير عمل جديد' }}</h2>
        </div>
        <form @submit.prevent="saveWorkflow" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم</label>
            <input v-model="workflowForm.name" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">نوع المستند</label>
            <select v-model="workflowForm.model_type" required :disabled="editingWorkflow" class="input-field">
              <option value="purchase_order">أمر شراء</option>
              <option value="supplier_bill">فاتورة مورد</option>
              <option value="employee_fund">عهدة موظف</option>
              <option value="fund_transfer">تحويل بنكي</option>
              <option value="customer_invoice">فاتورة عميل</option>
              <option value="expense">مصروف</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأدنى للمبلغ</label>
              <input v-model.number="workflowForm.min_amount" type="number" step="0.01" min="0" required class="input-field" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأقصى للمبلغ</label>
              <input v-model.number="workflowForm.max_amount" type="number" step="0.01" min="0" class="input-field" dir="ltr" placeholder="بدون حد">
            </div>
          </div>
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-sm font-medium text-gray-700">مستويات الاعتماد</label>
              <button type="button" @click="addLevel" class="text-blue-600 hover:text-blue-800 text-sm">+ إضافة مستوى</button>
            </div>
            <div v-for="(level, index) in workflowForm.approval_levels" :key="index" class="flex gap-2 mb-2">
              <span class="bg-gray-100 px-3 py-2 rounded text-sm">{{ index + 1 }}</span>
              <select v-model="level.role" required class="input-field flex-1">
                <option value="manager">مدير</option>
                <option value="finance_manager">مدير مالي</option>
                <option value="ceo">المدير التنفيذي</option>
                <option value="admin">مسؤول النظام</option>
              </select>
              <button v-if="workflowForm.approval_levels.length > 1" type="button" @click="removeLevel(index)" class="text-red-600 hover:text-red-800 px-2">×</button>
            </div>
          </div>
          <div class="flex items-center">
            <input v-model="workflowForm.is_active" type="checkbox" id="is_active" class="ml-2">
            <label for="is_active" class="text-sm">نشط</label>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="closeWorkflowForm" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from '@/utils/axios';

const activeTab = ref('pending');
const requests = ref([]);
const workflows = ref([]);
const stats = ref({ pending_count: 0, approved_today: 0, rejected_today: 0, avg_approval_time: 0 });
const selectedRequest = ref(null);
const editingWorkflow = ref(null);
const saving = ref(false);

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showWorkflowsModal = ref(false);
const showCreateWorkflow = ref(false);

const actionForm = ref({ comments: '' });
const workflowForm = ref({
  name: '', model_type: 'purchase_order', min_amount: 0, max_amount: null, is_active: true,
  approval_levels: [{ level: 1, role: 'manager', user_id: null }],
});

const loadRequests = async () => {
  try {
    const endpoint = activeTab.value === 'pending' ? '/api/approvals/pending' : '/api/approvals/my-requests';
    const [requestsRes, statsRes] = await Promise.all([
      axios.get(endpoint),
      axios.get('/api/approvals/statistics'),
    ]);
    requests.value = requestsRes.data.data || requestsRes.data;
    stats.value = statsRes.data;
  } catch (error) {
    console.error('Error loading requests:', error);
  }
};

const loadWorkflows = async () => {
  try {
    const response = await axios.get('/api/approvals/workflows');
    workflows.value = response.data;
  } catch (error) {
    console.error('Error loading workflows:', error);
  }
};

const openApproveModal = (request) => {
  selectedRequest.value = request;
  actionForm.value = { comments: '' };
  showApproveModal.value = true;
};

const openRejectModal = (request) => {
  selectedRequest.value = request;
  actionForm.value = { comments: '' };
  showRejectModal.value = true;
};

const approveRequest = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/approvals/requests/${selectedRequest.value.id}/approve`, actionForm.value);
    showApproveModal.value = false;
    await loadRequests();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const rejectRequest = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/approvals/requests/${selectedRequest.value.id}/reject`, actionForm.value);
    showRejectModal.value = false;
    await loadRequests();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewRequest = async (request) => {
  try {
    const response = await axios.get(`/api/approvals/requests/${request.id}`);
    selectedRequest.value = response.data;
    // Could open a detail modal here
  } catch (error) {
    console.error('Error loading request:', error);
  }
};

const addLevel = () => {
  workflowForm.value.approval_levels.push({ level: workflowForm.value.approval_levels.length + 1, role: 'manager', user_id: null });
};

const removeLevel = (index) => {
  workflowForm.value.approval_levels.splice(index, 1);
  workflowForm.value.approval_levels.forEach((l, i) => l.level = i + 1);
};

const editWorkflow = (workflow) => {
  editingWorkflow.value = workflow;
  workflowForm.value = { ...workflow, approval_levels: [...(workflow.approval_levels || [])] };
  showCreateWorkflow.value = true;
};

const saveWorkflow = async () => {
  saving.value = true;
  try {
    if (editingWorkflow.value) {
      await axios.put(`/api/approvals/workflows/${editingWorkflow.value.id}`, workflowForm.value);
    } else {
      await axios.post('/api/approvals/workflows', workflowForm.value);
    }
    closeWorkflowForm();
    await loadWorkflows();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const deleteWorkflow = async (workflow) => {
  if (!confirm('هل أنت متأكد من حذف سير العمل هذا؟')) return;
  try {
    await axios.delete(`/api/approvals/workflows/${workflow.id}`);
    await loadWorkflows();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const closeWorkflowForm = () => {
  showCreateWorkflow.value = false;
  editingWorkflow.value = null;
  workflowForm.value = {
    name: '', model_type: 'purchase_order', min_amount: 0, max_amount: null, is_active: true,
    approval_levels: [{ level: 1, role: 'manager', user_id: null }],
  };
};

const formatCurrency = (amount) => new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(amount || 0);
const formatDate = (date) => date ? new Date(date).toLocaleDateString('ar-SA') : '';

const getTypeLabel = (type) => {
  const labels = { purchase_order: 'أمر شراء', supplier_bill: 'فاتورة مورد', employee_fund: 'عهدة موظف', fund_transfer: 'تحويل بنكي', customer_invoice: 'فاتورة عميل', expense: 'مصروف' };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = { purchase_order: 'bg-blue-100 text-blue-800', supplier_bill: 'bg-red-100 text-red-800', employee_fund: 'bg-purple-100 text-purple-800', fund_transfer: 'bg-green-100 text-green-800', customer_invoice: 'bg-yellow-100 text-yellow-800', expense: 'bg-orange-100 text-orange-800' };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
  const labels = { pending: 'بانتظار الاعتماد', in_progress: 'قيد المراجعة', approved: 'معتمد', rejected: 'مرفوض', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { pending: 'bg-yellow-100 text-yellow-800', in_progress: 'bg-blue-100 text-blue-800', approved: 'bg-green-100 text-green-800', rejected: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

watch(activeTab, loadRequests);
watch(showWorkflowsModal, (val) => { if (val) loadWorkflows(); });

onMounted(loadRequests);
</script>
