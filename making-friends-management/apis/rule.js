import { request } from "./request"
export function getRuleList(page){
	let url = `/rule/${page}`
	return request({ url })
}

// 删除
export function deleteRule(id){
	return request({ 
		url:"/rule/delete",
		method:"POST",
		data:{
			id
		}
	})
}

// 创建
export function createRule(data){
	return request({ 
		url:"/rule/save",
		method:"POST",
		data
	})
}

// 更新
export function updateRule(id,data){
	return request({ 
		url:"/rule/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}