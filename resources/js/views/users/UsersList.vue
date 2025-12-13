<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <select v-model="filters.role" class="form-input w-40" @change="fetchUsers">
        <option value="">كل الأدوار</option>
        <option value="admin">مدير النظام</option>
        <option value="manager">مدير</option>
        <option value="sales">مبيعات</option>
        <option value="engineer">مهندس</option>
        <option value="accountant">محاسب</option>
      </select>
      <button @click="showModal = true" class="btn btn-primary">إضافة مستخدم</button>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>الدور</th><th>الحالة</th><th>إجراءات</th></tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="font-medium">{{ user.name }}</td>
              <td dir="ltr">{{ user.email }}</td>
              <td dir="ltr">{{ user.phone || '-' }}</td>
              <td><span class="badge badge-info">{{ roleLabel(user.role) }}</span></td>
              <td><span :class="user.is_active ? 'badge-success' : 'badge-danger'" class="badge">{{ user.is_active ? 'نشط' : 'غير نشط' }}</span></td>
              <td>
                <button @click="toggleActive(user)" class="text-sm" :class="user.is_active ? 'text-red-600' : 'text-green-600'">
                  {{ user.is_active ? 'تعطيل' : 'تفعيل' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- User Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة مستخدم</h3>
        <form @submit.prevent="createUser" class="space-y-4">
          <div><label class="form-label">الاسم *</label><input v-model="form.name" class="form-input" required /></div>
          <div><label class="form-label">البريد *</label><input v-model="form.email" type="email" class="form-input" dir="ltr" required /></div>
          <div><label class="form-label">كلمة المرور *</label><input v-model="form.password" type="password" class="form-input" dir="ltr" required /></div>
          <div><label class="form-label">الهاتف</label><input v-model="form.phone" class="form-input" dir="ltr" /></div>
          <div><label class="form-label">الدور *</label>
            <select v-model="form.role" class="form-input" required>
              <option value="admin">مدير النظام</option>
              <option value="manager">مدير</option>
              <option value="sales">مبيعات</option>
              <option value="engineer">مهندس</option>
              <option value="accountant">محاسب</option>
            </select>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const users = ref([]);
const filters = reactive({ role: '' });
const showModal = ref(false);
const form = reactive({ name: '', email: '', password: '', phone: '', role: 'sales' });

const fetchUsers = async () => {
  const { data } = await axios.get('/users', { params: filters });
  users.value = data.data;
};

const createUser = async () => {
  await axios.post('/users', form);
  showModal.value = false;
  Object.assign(form, { name: '', email: '', password: '', phone: '', role: 'sales' });
  fetchUsers();
};

const toggleActive = async (user) => {
  await axios.put(`/users/${user.id}`, { is_active: !user.is_active });
  fetchUsers();
};

const roleLabel = (r) => ({ admin: 'مدير النظام', manager: 'مدير', sales: 'مبيعات', engineer: 'مهندس', accountant: 'محاسب' }[r] || r);

onMounted(fetchUsers);
</script>
