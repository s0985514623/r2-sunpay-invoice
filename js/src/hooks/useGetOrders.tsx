import { useState, useEffect } from 'react'
import { useAjax } from '@/hooks'

type TProps = {
	startDate: string
	endDate: string
}

export const useGetOrders = <T extends TProps>(props: T) => {
	const [
		meta,
		setMeta,
	] = useState<{order_no:[],invoice_no:[]}|undefined>(undefined)
	const mutation = useAjax()
	const { mutate } = mutation
	useEffect(() => {
		mutate(
			{
				action: 'get_orders_list',
				startDate: props.startDate,
				endDate: props.endDate,
			},
			{
				onSuccess: (data) => {
					setMeta(data.data.data)
				},
				onError: (error) => {
					console.log(error)
				},
			},
		)
	}, [])

	return {
		...mutation,
		meta,
	}
}
