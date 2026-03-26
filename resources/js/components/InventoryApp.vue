<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const products = ref([])
const search = ref('')
const showForm = ref(false)
const editingProduct = ref(null)

const form = ref({ name: '', category: '', price: '', quantity: '' })

onMounted(async () => {
  const res = await axios.get('/api/products')
  products.value = res.data
})

const filteredProducts = computed(() =>
  products.value.filter(p =>
    p.name.toLowerCase().includes(search.value.toLowerCase()) ||
    p.category.toLowerCase().includes(search.value.toLowerCase())
  )
)

const totalItems = computed(() =>
  products.value.reduce((sum, p) => sum + p.quantity, 0)
)

const totalValue = computed(() =>
  products.value.reduce((sum, p) => sum + p.price * p.quantity, 0).toFixed(2)
)

const openAddForm = () => {
  editingProduct.value = null
  form.value = { name: '', category: '', price: '', quantity: '' }
  showForm.value = true
}

const openEditForm = (product) => {
  editingProduct.value = product
  form.value = { ...product }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingProduct.value = null
}

const saveProduct = async () => {
  if (!form.value.name || !form.value.category || form.value.price === '' || form.value.quantity === '') return

  if (editingProduct.value) {
    const res = await axios.put(`/api/products/${editingProduct.value.id}`, form.value)
    const index = products.value.findIndex(p => p.id === editingProduct.value.id)
    products.value[index] = res.data
  } else {
    const res = await axios.post('/api/products', form.value)
    products.value.unshift(res.data)
  }

  closeForm()
}

const deleteProduct = async (id) => {
  if (!confirm('Delete this product?')) return
  await axios.delete(`/api/products/${id}`)
  products.value = products.value.filter(p => p.id !== id)
}

const stockStatus = (qty) => {
  if (qty === 0) return 'out'
  if (qty <= 10) return 'low'
  return 'ok'
}
</script>

<template>
  <div class="container">
    <div class="header">
      <h1>📦 Product Inventory</h1>
      <button class="btn-add" @click="openAddForm">+ Add Product</button>
    </div>

    <div class="stats">
      <div class="stat-card">
        <span class="stat-label">Total Products</span>
        <span class="stat-value">{{ products.length }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Total Items in Stock</span>
        <span class="stat-value">{{ totalItems }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Total Inventory Value</span>
        <span class="stat-value">${{ totalValue }}</span>
      </div>
    </div>

    <input
      class="search"
      v-model="search"
      placeholder="Search by name or category..."
    />

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in filteredProducts" :key="product.id">
          <td>{{ product.name }}</td>
          <td><span class="badge">{{ product.category }}</span></td>
          <td>${{ Number(product.price).toFixed(2) }}</td>
          <td>{{ product.quantity }}</td>
          <td>
            <span :class="['status', stockStatus(product.quantity)]">
              {{ stockStatus(product.quantity) === 'ok' ? 'In Stock' : stockStatus(product.quantity) === 'low' ? 'Low Stock' : 'Out of Stock' }}
            </span>
          </td>
          <td class="actions">
            <button class="btn-edit" @click="openEditForm(product)">Edit</button>
            <button class="btn-delete" @click="deleteProduct(product.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="filteredProducts.length === 0">
          <td colspan="6" class="empty">No products found.</td>
        </tr>
      </tbody>
    </table>

    <!-- Modal Form -->
    <div class="modal-overlay" v-if="showForm" @click.self="closeForm">
      <div class="modal">
        <h3>{{ editingProduct ? 'Edit Product' : 'Add Product' }}</h3>
        <label>Name</label>
        <input v-model="form.name" placeholder="Product name" />
        <label>Category</label>
        <input v-model="form.category" placeholder="e.g. Electronics" />
        <label>Price ($)</label>
        <input v-model="form.price" type="number" step="0.01" placeholder="0.00" />
        <label>Quantity</label>
        <input v-model="form.quantity" type="number" placeholder="0" />
        <div class="modal-actions">
          <button class="btn-cancel" @click="closeForm">Cancel</button>
          <button class="btn-save" @click="saveProduct">
            {{ editingProduct ? 'Update' : 'Add Product' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
* { box-sizing: border-box; }

.container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: 'Segoe UI', sans-serif;
  color: #1a1a2e;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

h1 { font-size: 1.8rem; margin: 0; }

.btn-add {
  background: #4f46e5;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
}

.stats {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  flex: 1;
  background: #f0f0ff;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label { font-size: 0.8rem; color: #666; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #4f46e5; }

.search {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 0.95rem;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}

thead { background: #4f46e5; color: white; }
th { padding: 14px 16px; text-align: left; font-weight: 600; font-size: 0.9rem; }
td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fafafa; }

.badge {
  background: #e0e7ff;
  color: #4f46e5;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}
.status.ok     { background: #d1fae5; color: #065f46; }
.status.low    { background: #fef3c7; color: #92400e; }
.status.out    { background: #fee2e2; color: #991b1b; }

.actions { display: flex; gap: 8px; }

.btn-edit {
  background: #e0e7ff;
  color: #4f46e5;
  border: none;
  padding: 5px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
}

.btn-delete {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  padding: 5px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
}

.empty { text-align: center; color: #aaa; padding: 30px; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 28px;
  width: 420px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal h3 { margin: 0 0 8px; font-size: 1.2rem; }

.modal label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: -4px; }

.modal input {
  padding: 9px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
}

.modal-actions { display: flex; gap: 10px; margin-top: 8px; justify-content: flex-end; }

.btn-cancel {
  background: #f3f4f6;
  color: #444;
  border: none;
  padding: 9px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-save {
  background: #4f46e5;
  color: white;
  border: none;
  padding: 9px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}
</style>