<template>
	<view>
		<view class="flex items-center mb-3" v-for="(item,index) in values" :key="index">
			<el-image :src="values[index]" class="mr-3" style="width: 100px;height: 100px;"></el-image>
			<el-input clearable v-model="values[index]" placeholder="请输入图片地址" @input="handleInput">
			 <template #append>
				<el-button @click="remove(index)"><el-icon><DeleteFilled /></el-icon></el-button>
			  </template>
			</el-input>
		</view>
		<el-button @click="add">添加</el-button>
	</view>
</template>

<script>
	export default {
		emits:["update:modelValue"],
		name:"input-group",
		props:{
			modelValue:Array,
		},
		data() {
			return {
				values:[]
			};
		},
		created(){
			this.values = JSON.parse(JSON.stringify(this.modelValue))
		},
		methods: {
			add() {
				this.values.push("")
			},
			remove(index){
				this.$confirm("是否要删除该图片").then(res=>{
					this.values.splice(index,1)
					this.handleInput()
				})
			},
			handleInput(){
				this.$emit("update:modelValue",this.values)
			}
		},
	}
</script>

<style>

</style>