import { request } from "./request"
export function getIpImageList(page,only_nocheck = true){
	let url = `/ip_image/${page}?only_nocheck=${only_nocheck ? 1 : 0}`
	return request({ url })
}

// 审核
export function updateIpImages(id,status = 1){
	return request({ 
		url:"/ip_image/update",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id],
			status
		}
	})
}

// 删除
export function deleteIpImage(id){
	return request({ 
		url:"/ip_image/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}