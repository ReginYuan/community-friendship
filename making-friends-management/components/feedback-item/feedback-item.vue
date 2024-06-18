<template>
	<view class="chat-item">
		<el-avatar :size="40" :src="item.avatar" style="margin:0 20rpx;"></el-avatar>
		<view>
			<el-text>{{ item.name }}</el-text>
			<view class="chat-item-body chat-item-body-self mt-2">
				<text class="chat-item-text">{{ item.content }}</text>
				<view class="feedback-img" v-if="item.images.length > 0">
					<image v-for="(img,index) in item.images" :key="index" :src="img" mode="aspectFill" class="feedback-img-item" @click="previewImage(img)"></image>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		props: {
			item: Object,
		},
		data() {
			return {
				
			};
		},
		methods: {
			previewImage(img){
				uni.previewImage({
					current:img,
					urls:this.item.images
				})
			},
		},
	}
</script>

<style>
.chat-item {
	display:flex;
	flex-direction: row;
	padding: 25rpx 10rpx;
}
.chat-item-body {
	background-color: #f4f4f4;
	border-radius: 8px;
	padding: 20rpx;
	max-width: 475rpx;
}
.chat-item-self {
	flex-direction: row-reverse;
}
.chat-item-text {
	line-height: 1.6;
	font-size: 16px;
}
.chat-item-body-self {
	background-color: rgba(240,100,135,0.1);
}
.feedback-img {
	display: flex;
	flex-direction: row;
}
.feedback-img-item {
	width: 130rpx;height: 130rpx;padding: 5rpx;border-radius: 6rpx;
}
</style>