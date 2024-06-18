<template>
	<view class="flex py-3">
		<el-badge :value="badgeText" :hidden="status != 0">
			<el-avatar :size="30" :src="avatar" class="flex-shrink-0">
				<span style="font-size: 12px;" v-if="!avatar">{{ name }}</span>
			</el-avatar>
		</el-badge>
		<el-text class="mx-2">{{ name }}</el-text>
	</view>
</template>

<script>
	export default {
		name:"user-info",
		props:{
			name:{
				type:String,
				default:""
			},
			avatar:{
				type:String,
				default:""
			},
			status:{
				type:Number,
				default:1
			},
			badgeText:{
				type:String,
				default:"禁用"
			}
		}
	}
</script>

<style>

</style>