<template>
	<el-upload :multiple="limit > 1" :limit="limit" name="image" v-model:file-list="fileList" action="/adminapi/upload" :headers="headers"
		list-type="picture-card" :on-preview="handlePictureCardPreview" :on-remove="handleRemove" :on-success="handleSuccess">
		<el-icon>
			<Plus />
		</el-icon>
	</el-upload>
</template>

<script>
	import { useUserStore } from "@/stores/user"
	const userStore = useUserStore()
	export default {
		emits:["update:modelValue"],
		props:{
			modelValue:Array,
			limit:{
				type:Number,
				default:9
			}
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
				this.fileList = this.modelValue.map(url=>{
					return { 
						url,
						status:"success",
						response:{
							code:1,
							data:url
						}
					}
				})
			},
			handleSuccess(response, uploadFile, uploadFiles){
				if(response.code == 1){
					let urls = []
					uploadFiles.forEach(o=>{
						if(o.status == "success" && o.response){
							urls.push(o.response.data)
						}
					})
					this.$emit("update:modelValue",urls)
				}
			},
			handleRemove(uploadFile, uploadFiles) {
				let urls = []
				uploadFiles.forEach(o=>{
					if(o.status == "success" && o.response){
						urls.push(o.response.data)
					}
				})
				this.$emit("update:modelValue",urls)
			},
			handlePictureCardPreview(uploadFile){
				uni.previewImage({
					current:uploadFile.url,
					urls:this.modelValue
				})
			}
		},
	}
</script>
<style>
	
</style>