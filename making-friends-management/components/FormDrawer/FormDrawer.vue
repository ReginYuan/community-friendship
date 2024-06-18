<template>
    <el-drawer 
	v-model="showDrawer" 
    :title="drawerTitle" 
    :size="size" 
    :close-on-click-modal="false"
    :destroy-on-close="destroyOnClose"
	:z-index="998">
        <div class="formDrawer">
            <div class="body">
				<el-form :model="model" ref="formRef" :rules="rules" label-width="80px" :inline="false">
					<slot></slot>
				</el-form>
            </div>
            <div class="actions">
                <el-button type="primary" @click="submit" :loading="loading">{{ confirmText }}</el-button>
                <el-button type="default" @click="close">取消</el-button>
            </div>
        </div>
    </el-drawer>
</template>
<script>
	export default {
		emits:["submit"],
		props: {
			title:String,
			// 普通表单，并非新建修改
			isDefaultForm:{
				type:Boolean,
				default:false
			},
			size:{
				type:String,
				default:"50%"
			},
			destroyOnClose:{
				type:Boolean,
				default:true
			},
			confirmText:{
				type:String,
				default:"提交"
			},
			model:Object,
			rules:Object,
			createApiFun:Function,
			updateApiFun:Function
		},
		computed:{
			drawerTitle(){
				if(this.isDefaultForm){
					return this.title
				}
				return (this.editId ? "修改" : "创建") + this.title
			}
		},
		data() {
			return {
				showDrawer: false,
				loading:false,
				defaultForm:{},
				editId:0,
			}
		},
		created(){
			if(this.model){
				this.defaultForm = JSON.parse(JSON.stringify(this.model))
			}
		},
		methods: {
			showLoading() {
				this.loading = true
			},
			hideLoading(){
				this.loading = false
			},
			open(form,row = false){
				this.editId = row ? row.id : 0
				this.showDrawer = true
				this.reset(form,row)
			},
			close(){
				this.showDrawer = false
			},
			reset(form,row = false){
				if(this.$refs.formRef){
					this.$nextTick(()=>{
						this.$refs.formRef.clearValidate()
					})
				}
				for (const key in this.defaultForm) {
					form[key] = row ? row[key] : this.defaultForm[key]
				}
			},
			submit(){
				if(!this.model){
					this.$emit("submit")
					return
				}
				// 表单验证
				this.$refs.formRef.validate((valid) => {
					if (!valid) return
					
					if(!this.updateApiFun && !this.createApiFun){
						this.$emit("submit")
						return
					}
					
					// 表单验证
					const action = this.drawerTitle
					this.showLoading()
					
					// 修改还是创建操作
					let fun = null
					if(this.editId){
						fun = this.updateApiFun(this.editId, this.model)
					} else {
						fun = this.createApiFun(this.model)
					}
					
					fun.then(res => {
						this.$success(action + "成功")
						this.$emit("success",this.editId)
						this.close()
					}).finally(() => {
						this.hideLoading()
					})
				})
			}
		},
	}
</script>
<style>
	.el-drawer__title{
		font-size: 16px;
	}
    .formDrawer{
        width: 100%;
        height: 100%;
        position: relative;
        display: flex;
		flex-direction: column;
    }

    .formDrawer .body{
        flex: 1;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 50px;
        overflow-y: auto;
    }

    .formDrawer .actions{
        height: 50px;
		margin-top: auto;
		display: flex;
		align-items: center;
    }
</style>