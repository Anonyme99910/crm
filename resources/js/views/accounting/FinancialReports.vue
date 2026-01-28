<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">التقارير المالية</h1>
    </div>

    <!-- Report Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="border-b">
        <nav class="flex -mb-px">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
            :class="[activeTab === tab.id ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700', 'px-6 py-4 border-b-2 font-medium text-sm']">
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Date Filters -->
      <div class="p-4 border-b bg-gray-50">
        <div class="flex gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
            <input v-model="filters.from_date" type="date" class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
            <input v-model="filters.to_date" type="date" class="input-field">
          </div>
          <button @click="loadReport" class="btn-primary">تحديث</button>
          <button @click="printReport" class="btn-secondary">طباعة</button>
        </div>
      </div>

      <!-- Report Content -->
      <div class="p-6" id="report-content">
        <!-- Profit & Loss -->
        <div v-if="activeTab === 'pnl'" class="space-y-6">
          <h2 class="text-xl font-bold text-center mb-4">قائمة الدخل</h2>
          <p class="text-center text-gray-500 mb-6">للفترة من {{ filters.from_date }} إلى {{ filters.to_date }}</p>
          
          <div class="space-y-4">
            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold text-green-800 mb-3">الإيرادات</h3>
              <div v-for="item in pnlData.revenues" :key="item.account_code" class="flex justify-between py-1">
                <span>{{ item.account_name_ar || item.account_name }}</span>
                <span class="font-mono">{{ formatCurrency(item.amount) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-green-200 font-bold">
                <span>إجمالي الإيرادات</span>
                <span class="font-mono text-green-600">{{ formatCurrency(pnlData.total_revenue) }}</span>
              </div>
            </div>

            <div class="bg-red-50 p-4 rounded-lg">
              <h3 class="font-bold text-red-800 mb-3">المصروفات</h3>
              <div v-for="item in pnlData.expenses" :key="item.account_code" class="flex justify-between py-1">
                <span>{{ item.account_name_ar || item.account_name }}</span>
                <span class="font-mono">{{ formatCurrency(item.amount) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-red-200 font-bold">
                <span>إجمالي المصروفات</span>
                <span class="font-mono text-red-600">{{ formatCurrency(pnlData.total_expenses) }}</span>
              </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex justify-between font-bold text-lg">
                <span>صافي الربح / (الخسارة)</span>
                <span :class="pnlData.net_income >= 0 ? 'text-green-600' : 'text-red-600'" class="font-mono">
                  {{ formatCurrency(pnlData.net_income) }}
                </span>
              </div>
              <div class="flex justify-between text-sm text-gray-500 mt-2">
                <span>هامش الربح</span>
                <span>{{ pnlData.profit_margin }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Balance Sheet -->
        <div v-if="activeTab === 'balance'" class="space-y-6">
          <h2 class="text-xl font-bold text-center mb-4">الميزانية العمومية</h2>
          <p class="text-center text-gray-500 mb-6">كما في {{ filters.to_date }}</p>

          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-bold text-blue-800 mb-3">الأصول</h3>
                <div v-for="item in balanceData.assets" :key="item.account_code" class="flex justify-between py-1">
                  <span>{{ item.account_name_ar || item.account_name }}</span>
                  <span class="font-mono">{{ formatCurrency(item.balance) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-blue-200 font-bold">
                  <span>إجمالي الأصول</span>
                  <span class="font-mono text-blue-600">{{ formatCurrency(balanceData.total_assets) }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="bg-red-50 p-4 rounded-lg">
                <h3 class="font-bold text-red-800 mb-3">الالتزامات</h3>
                <div v-for="item in balanceData.liabilities" :key="item.account_code" class="flex justify-between py-1">
                  <span>{{ item.account_name_ar || item.account_name }}</span>
                  <span class="font-mono">{{ formatCurrency(item.balance) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-red-200 font-bold">
                  <span>إجمالي الالتزامات</span>
                  <span class="font-mono text-red-600">{{ formatCurrency(balanceData.total_liabilities) }}</span>
                </div>
              </div>

              <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-bold text-purple-800 mb-3">حقوق الملكية</h3>
                <div v-for="item in balanceData.equity" :key="item.account_code" class="flex justify-between py-1">
                  <span>{{ item.account_name_ar || item.account_name }}</span>
                  <span class="font-mono">{{ formatCurrency(item.balance) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-purple-200 font-bold">
                  <span>إجمالي حقوق الملكية</span>
                  <span class="font-mono text-purple-600">{{ formatCurrency(balanceData.total_equity) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-100 p-4 rounded-lg text-center">
            <span :class="balanceData.is_balanced ? 'text-green-600' : 'text-red-600'" class="font-bold">
              {{ balanceData.is_balanced ? '✓ الميزانية متوازنة' : '✗ الميزانية غير متوازنة' }}
            </span>
          </div>
        </div>

        <!-- Cash Flow -->
        <div v-if="activeTab === 'cashflow'" class="space-y-6">
          <h2 class="text-xl font-bold text-center mb-4">قائمة التدفقات النقدية</h2>
          <p class="text-center text-gray-500 mb-6">للفترة من {{ filters.from_date }} إلى {{ filters.to_date }}</p>

          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex justify-between font-bold">
                <span>الرصيد الافتتاحي</span>
                <span class="font-mono">{{ formatCurrency(cashFlowData.opening_balance) }}</span>
              </div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg">
              <h3 class="font-bold text-green-800 mb-3">الأنشطة التشغيلية</h3>
              <div class="flex justify-between py-1">
                <span>التدفقات الداخلة</span>
                <span class="font-mono text-green-600">{{ formatCurrency(cashFlowData.operating_activities?.inflows) }}</span>
              </div>
              <div class="flex justify-between py-1">
                <span>التدفقات الخارجة</span>
                <span class="font-mono text-red-600">({{ formatCurrency(cashFlowData.operating_activities?.outflows) }})</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-green-200 font-bold">
                <span>صافي التدفقات التشغيلية</span>
                <span class="font-mono">{{ formatCurrency(cashFlowData.operating_activities?.net) }}</span>
              </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex justify-between font-bold text-lg">
                <span>الرصيد الختامي</span>
                <span class="font-mono text-blue-600">{{ formatCurrency(cashFlowData.closing_balance) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Trial Balance -->
        <div v-if="activeTab === 'trial'" class="space-y-6">
          <h2 class="text-xl font-bold text-center mb-4">ميزان المراجعة</h2>
          <p class="text-center text-gray-500 mb-6">كما في {{ filters.to_date }}</p>

          <table class="min-w-full border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-right border">رمز الحساب</th>
                <th class="px-4 py-2 text-right border">اسم الحساب</th>
                <th class="px-4 py-2 text-right border">مدين</th>
                <th class="px-4 py-2 text-right border">دائن</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in trialData.accounts" :key="item.account_code" class="border-t">
                <td class="px-4 py-2 border font-mono">{{ item.account_code }}</td>
                <td class="px-4 py-2 border">{{ item.account_name_ar || item.account_name }}</td>
                <td class="px-4 py-2 border text-green-600 font-mono">{{ item.debit > 0 ? formatCurrency(item.debit) : '' }}</td>
                <td class="px-4 py-2 border text-red-600 font-mono">{{ item.credit > 0 ? formatCurrency(item.credit) : '' }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-100 font-bold">
              <tr>
                <td colspan="2" class="px-4 py-2 border">الإجمالي</td>
                <td class="px-4 py-2 border text-green-600 font-mono">{{ formatCurrency(trialData.total_debit) }}</td>
                <td class="px-4 py-2 border text-red-600 font-mono">{{ formatCurrency(trialData.total_credit) }}</td>
              </tr>
            </tfoot>
          </table>

          <div class="text-center">
            <span :class="trialData.is_balanced ? 'text-green-600' : 'text-red-600'" class="font-bold">
              {{ trialData.is_balanced ? '✓ الميزان متوازن' : '✗ الميزان غير متوازن' }}
            </span>
          </div>
        </div>

        <!-- Project Profitability -->
        <div v-if="activeTab === 'projects'" class="space-y-6">
          <h2 class="text-xl font-bold text-center mb-4">ربحية المشاريع</h2>

          <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white border rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-blue-600">{{ projectsData.summary?.total_projects }}</div>
              <div class="text-sm text-gray-500">إجمالي المشاريع</div>
            </div>
            <div class="bg-white border rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-green-600">{{ formatCurrency(projectsData.summary?.total_revenue) }}</div>
              <div class="text-sm text-gray-500">إجمالي الإيرادات</div>
            </div>
            <div class="bg-white border rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-red-600">{{ formatCurrency(projectsData.summary?.total_expenses) }}</div>
              <div class="text-sm text-gray-500">إجمالي المصروفات</div>
            </div>
            <div class="bg-white border rounded-lg p-4 text-center">
              <div class="text-2xl font-bold" :class="projectsData.summary?.total_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(projectsData.summary?.total_profit) }}
              </div>
              <div class="text-sm text-gray-500">صافي الربح</div>
            </div>
          </div>

          <table class="min-w-full border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-right border">المشروع</th>
                <th class="px-4 py-2 text-right border">الإيرادات</th>
                <th class="px-4 py-2 text-right border">المصروفات</th>
                <th class="px-4 py-2 text-right border">الربح</th>
                <th class="px-4 py-2 text-right border">الهامش %</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="project in projectsData.projects" :key="project.id" class="border-t hover:bg-gray-50">
                <td class="px-4 py-2 border">{{ project.name }}</td>
                <td class="px-4 py-2 border font-mono">{{ formatCurrency(project.total_revenue) }}</td>
                <td class="px-4 py-2 border font-mono">{{ formatCurrency(project.total_expenses) }}</td>
                <td class="px-4 py-2 border font-mono" :class="project.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(project.gross_profit) }}
                </td>
                <td class="px-4 py-2 border">{{ project.profit_margin }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from '@/utils/axios';

const tabs = [
  { id: 'pnl', name: 'قائمة الدخل' },
  { id: 'balance', name: 'الميزانية العمومية' },
  { id: 'cashflow', name: 'التدفقات النقدية' },
  { id: 'trial', name: 'ميزان المراجعة' },
  { id: 'projects', name: 'ربحية المشاريع' },
];

const activeTab = ref('pnl');
const filters = ref({
  from_date: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
  to_date: new Date().toISOString().split('T')[0],
});

const pnlData = ref({ revenues: [], expenses: [], total_revenue: 0, total_expenses: 0, net_income: 0, profit_margin: 0 });
const balanceData = ref({ assets: [], liabilities: [], equity: [], total_assets: 0, total_liabilities: 0, total_equity: 0, is_balanced: true });
const cashFlowData = ref({ opening_balance: 0, closing_balance: 0, operating_activities: {} });
const trialData = ref({ accounts: [], total_debit: 0, total_credit: 0, is_balanced: true });
const projectsData = ref({ projects: [], summary: {} });

const loadReport = async () => {
  try {
    switch (activeTab.value) {
      case 'pnl':
        const pnlRes = await axios.get('/api/reports/financial/profit-loss', { params: filters.value });
        pnlData.value = pnlRes.data;
        break;
      case 'balance':
        const balanceRes = await axios.get('/api/reports/financial/balance-sheet', { params: { as_of_date: filters.value.to_date } });
        balanceData.value = balanceRes.data;
        break;
      case 'cashflow':
        const cashRes = await axios.get('/api/reports/financial/cash-flow', { params: filters.value });
        cashFlowData.value = cashRes.data;
        break;
      case 'trial':
        const trialRes = await axios.get('/api/accounting/accounts/trial-balance');
        trialData.value = trialRes.data;
        break;
      case 'projects':
        const projRes = await axios.get('/api/reports/financial/project-profitability');
        projectsData.value = projRes.data;
        break;
    }
  } catch (error) {
    console.error('Error loading report:', error);
  }
};

const printReport = () => {
  const content = document.getElementById('report-content').innerHTML;
  const reportTitle = tabs.find(t => t.id === activeTab.value)?.name || 'تقرير مالي';
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>${reportTitle}</title>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
          
          * { box-sizing: border-box; margin: 0; padding: 0; }
          
          body { 
            font-family: 'Cairo', 'Segoe UI', Tahoma, Arial, sans-serif;
            padding: 40px;
            background: #fff;
            color: #1f2937;
            direction: rtl;
            line-height: 1.6;
          }
          
          .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #d4af37;
          }
          
          .header h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 10px;
          }
          
          .header .company-name {
            font-size: 18px;
            color: #d4af37;
            font-weight: 600;
          }
          
          .header .date-range {
            font-size: 14px;
            color: #6b7280;
            margin-top: 10px;
          }
          
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
          }
          
          th, td { 
            border: 1px solid #e5e7eb; 
            padding: 12px 16px; 
            text-align: right; 
          }
          
          th { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #374151;
          }
          
          tr:nth-child(even) { background-color: #f9fafb; }
          tr:hover { background-color: #f3f4f6; }
          
          .section {
            margin: 25px 0;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
          }
          
          .section-green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #86efac; }
          .section-red { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-color: #fca5a5; }
          .section-blue { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-color: #93c5fd; }
          .section-purple { background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-color: #d8b4fe; }
          
          .section h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid currentColor;
          }
          
          .section-green h3 { color: #166534; border-color: #22c55e; }
          .section-red h3 { color: #991b1b; border-color: #ef4444; }
          .section-blue h3 { color: #1e40af; border-color: #3b82f6; }
          .section-purple h3 { color: #6b21a8; border-color: #a855f7; }
          
          .row { display: flex; justify-content: space-between; padding: 8px 0; }
          .row-total { border-top: 2px solid currentColor; margin-top: 10px; padding-top: 10px; font-weight: 700; }
          
          .text-green-600 { color: #16a34a; }
          .text-red-600 { color: #dc2626; }
          .text-blue-600 { color: #2563eb; }
          .text-purple-600 { color: #9333ea; }
          
          .font-bold { font-weight: 700; }
          .font-mono { font-family: 'Courier New', monospace; direction: ltr; display: inline-block; }
          
          .text-center { text-align: center; }
          .text-xl { font-size: 20px; }
          .text-lg { font-size: 18px; }
          .text-sm { font-size: 14px; }
          
          .grid { display: grid; gap: 20px; }
          .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
          .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
          
          .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
          }
          
          .stat-card .value { font-size: 24px; font-weight: 700; }
          .stat-card .label { font-size: 12px; color: #6b7280; margin-top: 5px; }
          
          .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
          }
          
          @media print {
            body { padding: 20px; }
            .section { break-inside: avoid; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <div class="company-name">حازم عبدالله للتشطيبات</div>
          <h1>${reportTitle}</h1>
          <div class="date-range">للفترة من ${filters.value.from_date} إلى ${filters.value.to_date}</div>
        </div>
        ${content}
        <div class="footer">
          تم إنشاء هذا التقرير بتاريخ ${new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' })}
        </div>
      </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => printWindow.print(), 500);
};

const formatCurrency = (amount) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount || 0);

watch(activeTab, loadReport);
onMounted(loadReport);
</script>
