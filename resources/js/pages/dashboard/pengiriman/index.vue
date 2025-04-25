<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import Swal from "sweetalert2";
import type { Pengiriman } from "@/types";

const column = createColumnHelper<Pengiriman>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deletePengiriman } = useDelete({
  onSuccess: () => refresh(),
});

// const showRincian = (data: Pengiriman) => {
//   alert(`
//     No Resi: ${data.no_resi}
//     Paket: ${data.paket}
//     Kurir: ${data.name}
//     Penerima: ${data.penerima}
//     Alamat: ${data.alamat}
//     Tanggal Pengiriman: ${data.tanggal_pengiriman}
//     Tanggal Penerimaan: ${data.tanggal_penerimaan || "-"}
//     Status: ${data.status}
//   `);
// };
const showRincian = (data: Pengiriman) => {
  Swal.fire({
    title: `<strong>Detail Pengiriman</strong>`,
    html: `
      <div style="text-align: left;">
        <p><b>No Resi:</b> ${data.no_resi}</p>
        <p><b>Paket:</b> ${data.paket}</p>
        <p><b>Kurir:</b> ${data.name}</p>
        <p><b>Penerima:</b> ${data.penerima}</p>
        <p><b>Alamat:</b> ${data.alamat}</p>
        <p><b>Tanggal Dibuat:</b> ${data.Tanggal_dibuat || "-"}</p>
        <p><b>Tanggal Pengiriman:</b> ${data.tanggal_pengiriman || "-"}</p>
        <p><b>Tanggal Penerimaan:</b> ${data.tanggal_penerimaan || "-"}</p>
        <p><b>Status:</b> ${data.status}</p>
      </div>
    `,
    // icon: "info",
    confirmButtonText: "Tutup",
    // customClass: {
    //   popup: 'text-start',
    // },
  });
};

const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("no_resi", { header: "No. Resi" }),
  column.accessor("paket", { header: "Paket" }),
  column.display({
    id: "rincian",
    header: "Detail Pengiriman",
    cell: (cell) =>
      h(
        "button",
        {
          class: "btn btn-sm btn-info",
          onClick: () => showRincian(cell.row.original),
        },
        "Lihat Detail"
      ),
  }),
  //   column.display({
  //   id: "rincian",
  //   header: "Tracking",
  //   cell: (cell) =>
  //     h(
  //       resolveComponent("RouterLink"),
  //       {
  //         to: `/dashboard/tracking?resi=${cell.row.original.no_resi}`,
  //         class: "btn btn-sm btn-info",
  //       },
  //       () => "Tracking"
  //     ),
  // }),
  column.accessor("id", {
    header: "Aksi",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-danger",
            onClick: () => {
              deletePengiriman(`/pengiriman/${cell.getValue()}`);
            },
          },
          h("i", { class: "la la-trash fs-2" })
        ),
      ]),
  }),
];


const refresh = () => paginateRef.value?.refetch();

watch(openForm, (val) => {
  if (!val) {
    selected.value = "";
  }
  window.scrollTo(0, 0);
});
</script>

<template>
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Pengiriman</h2>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengiriman" url="/pengiriman" :columns="columns"></paginate>
    </div>
  </div>
</template>
