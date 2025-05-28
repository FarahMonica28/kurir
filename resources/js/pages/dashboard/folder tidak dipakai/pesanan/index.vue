<script setup lang="ts">
import { h, ref, watch } from 'vue'
import { createColumnHelper } from '@tanstack/vue-table'
import { useDelete } from '@/libs/hooks'
import Form from './form.vue'
import type { Pesanan } from '@/types'
import Swal from 'sweetalert2'

const column = createColumnHelper<Pesanan>()
const paginateRef = ref<any>(null)
const selected = ref<string>("")
const openForm = ref<boolean>(false)

const { delete: deletePesanan } = useDelete({
  onSuccess: () => refresh(),
})

const showDetail = (data: Pesanan) => {
  Swal.fire({
    title: `Detail Pesanan`,
    html: `
      <div style="text-align:left;">
        <p><b>No Pesanan:</b> ${data.no_pesanan}</p>
        <p><b>Tanggal:</b> ${data.tanggal}</p>
        <p><b>Pelanggan:</b> ${data.nama_pelanggan}</p>
        <p><b>Produk:</b> ${data.produk}</p>
        <p><b>Total:</b> Rp${data.total}</p>
        <p><b>Status:</b> ${data.status}</p>
      </div>
    `,
    confirmButtonText: 'Tutup'
  })
}

const columns = [
  column.accessor('no', { header: '#' }),
  column.accessor('no_pesanan', { header: 'No. Pesanan' }),
  column.accessor('tanggal', { header: 'Tanggal' }),
  column.accessor('nama_pelanggan', { header: 'Pelanggan' }),
  column.accessor('produk', { header: 'Produk' }),
  column.accessor('total', { header: 'Total' }),
  column.accessor('status', { header: 'Status' }),
  column.display({
    id: 'aksi',
    header: 'Aksi',
    cell: (cell) =>
      h('div', { class: 'd-flex gap-2' }, [
        h(
          'button',
          {
            class: 'btn btn-sm btn-info',
            onClick: () => showDetail(cell.row.original)
          },
          'Detail'
        ),
        h(
          'button',
          {
            class: 'btn btn-sm btn-danger',
            onClick: () => deletePesanan(`/pesanan/${cell.row.original.id}`)
          },
          h('i', { class: 'la la-trash fs-2' })
        )
      ])
  })
]

const refresh = () => paginateRef.value?.refetch()

watch(openForm, (val) => {
  if (!val) selected.value = ""
  window.scrollTo(0, 0)
})
</script>

<template>
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center justify-between d-flex">
      <h2 class="mb-0">Daftar Pesanan</h2>
      <button class="btn btn-primary" @click="openForm = true">+ Tambah Pesanan</button>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pesanan" url="/pesanan" :columns="columns" />
    </div>
  </div>
</template>
