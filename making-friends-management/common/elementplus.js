import ElementPlus from 'element-plus'
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'
import { 
	ElMessageBox,
	ElNotification,
	ElLoading,
} from 'element-plus'
import 'element-plus/dist/index.css'
export default {
	install(app){
		// 全局设置
		app.use(ElementPlus,{ 
			locale: zhCn
		})
		// 全局属性
		app.config.globalProperties.$confirm = ElMessageBox.confirm
		app.config.globalProperties.$prompt = ElMessageBox.prompt
		app.config.globalProperties.$success = (message,title = "")=>{
			return ElNotification({
				title,
				message,
				type: 'success',
			})
		}
		app.config.globalProperties.$error = (message,title = "")=>{
			return ElNotification({
				title,
				message,
				type: 'error',
			})
		}
		app.config.globalProperties.$warning = (message,title = "")=>{
			return ElNotification({
				title,
				message,
				type: 'warning',
			})
		}
		// loading
		let loading = null
		app.config.globalProperties.$loading = {
			show:()=>{
				loading = ElLoading.service({ fullscreen: true })
			},
			hide:()=>{
				if(loading){
					loading.close()
				}
			}
		}
	}
}