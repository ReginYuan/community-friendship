import App from './App'

// #ifndef VUE3
import Vue from 'vue'
import './uni.promisify.adaptor'
Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
  ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
import elementplus from '@/common/elementplus'
import request from '@/apis/request'
import * as Pinia from 'pinia';
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'virtual:uno.css'
import permission from "@/common/permission.js"
export function createApp() {
  const app = createSSRApp(App)
  app.use(Pinia.createPinia());
  app.use(request)
  app.use(permission)
  app.use(elementplus)
  for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
    app.component(key, component)
  }
  return {
    app,
	Pinia
  }
}
// #endif