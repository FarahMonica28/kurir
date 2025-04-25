import { ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

export function useKurir() {
  const data = ref([]);
  const loading = ref(false);

  const fetch = () => {
    loading.value = true;
    ApiService.get("/kurir/list")
      .then((res) => {
        data.value = res.data.kurir || [];
      })
      .finally(() => {
        loading.value = false;
      });
  };

  onMounted(fetch);

  return {
    data,
    loading,
    refetch: fetch,
  };
}
