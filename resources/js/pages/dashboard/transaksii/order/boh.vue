<!-- <script>

const provinceDestination = ref("0");



// const provinceDestination = ref<{ id: string; name: string }[]>([])
const searchProvinceDestination = ref("");
// const showDropdownDestination = ref(false);
const showProvinceDropdownDestination = ref(true)
const filteredProvincesDestination = computed(() => {
    return provinceDestination.value.filter((prov) =>
    prov.name.toLowerCase().includes(searchProvinceDestination.value.toLowerCase())
    );
});
// const hideDropdownWithDelay = () => {
//   setTimeout(() => {
//     showDropdownDestination.value = false;
//   }, 200);
// };
const hideProvinceDropdownDestinationWithDelay = () => {
  setTimeout(() => {
    showProvinceDropdownDestination.value = false
  }, 200)
}
const selectProvinceDestination = (prov: { id: string; name: string }) => {
  searchProvinceDestination.value = prov.name;
  provinceDestination.value = prov.id;
  setFieldValue("provinceDestination", prov.id); // agar tersimpan di form
  fetchCities("destination");
  showDropdownDestination.value = false;
};

const limitProvinceDestination = ref(10);
const displayedProvincesDestination = computed(() => {
    filteredProvincesDestination.value.slice(0, limitProvinceDestination.value);
});
//itu atas
const loadMoreProvincesDestination = () => {
  if (limitProvinceDestination.value < filteredProvincesDestination.value.length) {
    limitProvinceDestination.value += 10;
  }
};
watch(searchProvinceDestination, () => {
  limitProvinceDestination.value = 10;
});



// tujuan provinsi
// const provinceDestination = ref<{ id: string; name: string }[]>([])
const searchProvinceDestination = ref("");
const provinceDestination = ref("0");
const showDropdownDestination = ref(false);
const showProvinceDropdownDestination = ref(false)
const filteredProvincesDestination = computed(() => {
    return provinceDestination.value.filter((prov) =>
    prov.name.toLowerCase().includes(searchProvinceDestination.value.toLowerCase())
  );
});
// const hideDropdownWithDelay = () => {
//   setTimeout(() => {
//     showDropdownDestination.value = false;
//   }, 200);
// };
const hideProvinceDropdownDestinationWithDelay = () => {
  setTimeout(() => {
    showProvinceDropdownDestination.value = false
  }, 200)
}
const selectProvinceDestination = (prov: { id: string; name: string }) => {
  searchProvinceDestination.value = prov.name;
  provinceDestination.value = prov.id;
  setFieldValue("provinceDestination", prov.id); // agar tersimpan di form
  fetchCities("destination");
  showDropdownDestination.value = false;
};
const limitProvinceDestination = ref(10);
const displayedProvincesDestination = computed(() => {
  filteredProvincesDestination.value.slice(0, limitProvinceDestination.value);
});
const loadMoreProvincesDestination = () => {
  if (limitProvinceDestination.value < filteredProvincesDestination.value.length) {
    limitProvinceDestination.value += 10;
  }
};
watch(searchProvinceDestination, () => {
  limitProvinceDestination.value = 10;
});/
</script> -->









<script>
const cityDestination = ref("");
const citiesDestination = ref<Record<string, string>>({})
const searchCityDestination = ref("");
const showCityDropdownDestination = ref(false);
const filteredCitiesDestination = computed(() => {
  return citiesDestination.value.filter((city) =>
    city.name.toLowerCase().includes(searchCityDestination.value.toLowerCase())
  );
});
const selectCityDestination = (city: { id: string; name: string }) => {
  searchCityDestination.value = city.name;
  cityDestination.value = city.id;
  setFieldValue("cityDestination", city.id); // sync ke VeeValidate
  showCityDropdownDestination.value = false;
  fetchDistricts("destination");
};
const hideCityDropdownWithDelay = () => {
  setTimeout(() => {
    showCityDropdownDestination.value = false;
  }, 200);
};
watch(searchCityDestination, () => {
  limitCityDestination.value = 10;
});
// const limitCityOrigin = ref(10);
// const displayedCitiesOrigin = computed(() =>
//   filteredCitiesOrigin.value.slice(0, limitCityOrigin.value)
// );
// const loadMoreCitiesOrigin = () => {
//   if (limitCityOrigin.value < filteredCitiesOrigin.value.length) {
//     limitCityOrigin.value += 10;
//   }
// };
</script>
