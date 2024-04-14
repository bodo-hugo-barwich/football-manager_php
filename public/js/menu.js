import { createApp } from "/js/vue/petite-vue.es.js";

function toggleMenu() {
  this.menu.show = !this.menu.show;
}

function mounted(show) {
  console.log("menuApp - mounted() - go ...");
  console.log("show:", show);

  this.menu.show = show;

  console.log("menuApp - mounted() - done.");
}

// Vue.JS Engine Object
let menuApp = createApp({
  menu: { show: false },
  mounted,
  toggleMenu,
}).mount("#menu-app");

// Vue.JS Application
console.log(menuApp);

console.log("menuApp - done.");
