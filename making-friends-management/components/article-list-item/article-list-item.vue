<template>
	<view class="list-item-header mb-2">
		<el-badge value="封禁" :hidden="item.user_status != 0" style="margin-right: 27rpx;flex-shrink:0;">
			<el-avatar class="my-3" :src="item.avatar"/>
		</el-badge>
		
		<view>
			<p class="nickname">{{ item.name }}</p>
			<p class="time">{{ item.create_time }} · {{ item.read_count }}次阅读</p>
		</view>
	</view>
	
	<text class="content">{{ item.content }}</text>
	
	<!-- 单图 -->
	<view class="content-media">
		<image :fade-show="true" v-for="(img,imgI) in item.images" :key="imgI" :src="img" mode="aspectFill" class="content-image more-image" @click="preview(img)"></image>
	</view>
	<view class="mt-3">
		<el-tag type="success" v-if="item.category_name != null"> {{ item.category_name }}</el-tag>
	</view>
	<view class="mt-3">
		<el-tag type="danger" v-if="item.topic_name != null"># {{ item.topic_name }}</el-tag>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				
			};
		},
		props: {
			item: {
				type: Object,
				required:true
			},
		},
		methods: {
			preview(img) {
				uni.previewImage({
					current:img,
					urls:this.item.images
				})
			}
		},
	}
</script>

<style>
	.list-item-header {
		display: flex;
		flex-direction: row;
		align-items: center;
		padding-top: 26rpx;
		padding-left: 33rpx;
		padding-right: 33rpx;
	}

	.nickname {
		font-size: 15px;
		font-weight: bold;
		color: #202020;
	}
	
	.icon-xialazhankai {
		padding: 10rpx;
		color: #C9C9C9;
	}
	
	.content {
		padding-top: 22rpx;
		padding-left: 33rpx;
		padding-right: 33rpx;
		font-size: 16px;
		line-height: 1.5;
	}
	
	.content-media {
		margin-top: 17rpx;
		padding-left: 10rpx;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
	}
	
	.content-image {
		margin-top: 10rpx;
		margin-right: 10rpx;
		background-color: #f0f0f0;
	}
	
	.one-image {
		width: 730rpx;
		height: 730rpx;
	}
	
	.two-image {
		width: 360rpx;height: 360rpx;
	}
	
	.more-image {
		width: 236rpx;height: 236rpx;
	}
	
	.topic-tag {
		display: flex;
		flex-direction: row;margin:0 30rpx;background-color: #fff3f7;border-radius: 50px;padding:8rpx 20rpx;align-items: center;margin-top: 25rpx;align-self: flex-start;
	}
	
	.topic-tag-icon {
		color: #fb5d7d;font-weight: bold;margin:0 10rpx;font-size: 18px;
	}
	
	.topic-tag-title {
		color: #221d1e;font-size: 14px;
	}

	.topic-tag-more {
		color: #63585a;
	}

	.actions {
		display: flex;
		flex-direction: row; 
		align-items: center;
		height: 105rpx;
	}
	
	.delete-action {
		margin-left: auto;padding: 10rpx;color: #999999;
	}
</style>