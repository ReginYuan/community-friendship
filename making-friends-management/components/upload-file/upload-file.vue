<template>
	<el-upload
	    v-model:file-list="fileList"
		action="/adminapi/upgradation/upload" 
	    :on-remove="handleRemove"
	    :limit="1"
		:headers="headers"
		name="file"
		:on-success="handleSuccess"
	  >
	    <el-button type="primary" :disabled="fileList.length == 1">上传安装包</el-button>
	    <template #tip>
	      <div class="el-upload__tip">
	        大小小于100M的apk文件
	      </div>
	    </template>
	  </el-upload>
</template>

<script>
	import { useUserStore } from "@/stores/user"
	const userStore = useUserStore()
	export default {
		emits:["update:modelValue"],
		props:{
			modelValue:String,
		},
		data() {
			return {
				fileList: [],
			}
		},
		created() {
			this.setFileList()
		},
		computed: {
			headers() {
				return {
					token:userStore.token
				}
			}
		},
		methods: {
			setFileList(){
				if(this.modelValue){
					this.fileList = [{
						name:this.modelValue,
						url:this.modelValue,
						status:"success",
						response:{
							code:1,
							data:this.modelValue
						}
					}]
				}
			},
			handleSuccess(response, uploadFile, uploadFiles){
				if(response.code == 1){
					let urls = []
					uploadFiles.forEach(o=>{
						if(o.status == "success" && o.response){
							urls.push(o.response.data)
						}
					})
					this.$emit("update:modelValue",urls[0])
				}
			},
			handleRemove(uploadFile, uploadFiles) {
				let urls = []
				uploadFiles.forEach(o=>{
					if(o.status == "success" && o.response){
						urls.push(o.response.data)
					}
				})
				this.$emit("update:modelValue",urls[0])
			},
		},
	}
</script>
<style>
	
</style>