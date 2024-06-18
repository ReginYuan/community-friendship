import {
	defineConfig
} from 'vite';
import uni from '@dcloudio/vite-plugin-uni';
// import AutoImport from 'unplugin-auto-import/vite'
// import Components from 'unplugin-vue-components/vite'
// import {
// 	ElementPlusResolver
// } from 'unplugin-vue-components/resolvers'
import UnoCSS from 'unocss/vite'
export default defineConfig({
	plugins: [
		uni(),
		// AutoImport({
		// 	resolvers: [ElementPlusResolver()],
		// }),
		// Components({
		// 	resolvers: [ElementPlusResolver()],
		// }),
		UnoCSS(),
	],
	server: {
		proxy: {
			'/adminapi': {
				target: "http://127.0.0.1:8000/admin",
				// target: "http://sqapi2.dishawang.com/admin",
				changeOrigin: true,
				rewrite: (path) => path.replace(/^\/adminapi/, '')
			},
		}
	}
});