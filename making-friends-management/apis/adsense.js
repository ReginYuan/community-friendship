import { request } from "./request"
export function getAdsenseList(page){
	let url = `/adsense/${page}`
	return request({ url })
}

// 删除广告位
export function deleteAdsense(id){
	return request({ 
		url:"/adsense/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建广告位
export function createAdsense(data){
	return request({ 
		url:"/adsense/save",
		method:"POST",
		data
	})
}

// 更新广告位
export function updateAdsense(id,data){
	return request({ 
		url:"/adsense/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}