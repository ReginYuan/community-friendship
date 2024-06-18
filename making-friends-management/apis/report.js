import { request } from "./request"
export function getReportList(page){
	let url = `/report/${page}`
	return request({ url })
}

// 删除举报
export function deleteReport(id){
	return request({ 
		url:"/report/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 更新举报
export function updateReport(id,data){
	return request({ 
		url:"/report/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}