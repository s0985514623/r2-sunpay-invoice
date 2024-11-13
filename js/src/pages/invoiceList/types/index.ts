import {Dayjs } from 'dayjs'

export type TFilter = {
	dateRange: [Dayjs, Dayjs]
	invoiceNos: string[]
	orderNos: string[]
}
export type TModalProps = {
	visible: boolean
	setVisible: React.Dispatch<React.SetStateAction<boolean>>
	dataSource:
		|{
				description: string
				quantity: number
				unit: string
				unitPrice: number
				amount: number
				remark: string
				taxType: number
		  }[]
		| []
}