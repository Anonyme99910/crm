<template>
  <div class="max-w-2xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إعدادات النظام</h3></div>
      <div class="card-body space-y-6">
        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium mb-4">بيانات الشركة</h4>
          <div class="space-y-4">
            <div><label class="form-label">اسم الشركة</label><input v-model="settings.company_name" class="form-input" /></div>
            <div><label class="form-label">الهاتف</label><input v-model="settings.company_phone" class="form-input" dir="ltr" /></div>
            <div><label class="form-label">البريد</label><input v-model="settings.company_email" class="form-input" dir="ltr" /></div>
            <div><label class="form-label">العنوان</label><textarea v-model="settings.company_address" class="form-input" rows="2"></textarea></div>
          </div>
        </div>

        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium mb-4">الإعدادات المالية</h4>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="form-label">نسبة الضريبة %</label><input v-model="settings.tax_rate" type="number" class="form-input" min="0" max="100" /></div>
            <div><label class="form-label">العملة</label><input v-model="settings.currency" class="form-input" /></div>
          </div>
        </div>

        <button @click="saveSettings" class="btn btn-primary">حفظ الإعدادات</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';

const settings = reactive({
  company_name: 'شركة التشطيبات المتميزة',
  company_phone: '01000000000',
  company_email: 'info@company.com',
  company_address: 'القاهرة - مصر',
  tax_rate: 14,
  currency: 'EGP'
});

const saveSettings = () => {
  alert('تم حفظ الإعدادات بنجاح');
};
</script>
