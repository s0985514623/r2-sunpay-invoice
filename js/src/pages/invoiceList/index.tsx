import '@/assets/scss/index.scss'
import { useState } from 'react'
import { Table, Button } from 'antd'
import Filter from './Filter'
import ItemsModal from './Modal'
import { TFilter, TModalProps } from './types'
import { useAjax } from '@/hooks'

const InvoiceList: React.FC = () => {
	const mutation = useAjax()
	const { mutate, isLoading } = mutation
	const [data, setData] = useState<any>([])
	const [modalVisible, setModalVisible] = useState(false)
	const [modalData, setModalData] = useState<TModalProps['dataSource']>([])

	//處理Filter 返回的資料
	const handleFilterSet = (values: TFilter) => {
		mutate(
			{
				action: 'get_invoice_list',
				invoiceNumbers: values.invoiceNos
					? values.invoiceNos
					: '',
				orderNos: values.orderNos ? values.orderNos : '',
			},
			{
				onSuccess: (data) => {
					const formatData = data.data.data?.result?.map((item: any) => ({
						...item,
						key: item.invoiceNumber,
					}))
					setData(formatData)
				},
				onError: (error) => {
					console.log(error)
				},
			},
		)
	}


	return (
		<div className="w-full relative">
			<h1>發票查詢</h1>
			<div className="pr-5 flex flex-col gap-10">
				<ItemsModal
					dataSource={modalData}
					visible={modalVisible}
					setVisible={setModalVisible}
				/>
				<Filter onFilter={handleFilterSet} isOutLoading={isLoading} />
				<Table dataSource={data}>
					<Table.Column title="開立日期" dataIndex="crT_DAT" width={125} />
					<Table.Column
						title="發票號碼"
						dataIndex="invoiceNumber"
						width={125}
					/>
					<Table.Column
						title="買受人統編"
						dataIndex="buyerIdentifier"
						width={125}
					/>
					<Table.Column title="買受人名稱" dataIndex="buyerName" width={125} />
					<Table.Column title="發票金額" dataIndex="totalAmount" width={125} />
					<Table.Column title="訂單編號" dataIndex="orderNo" width={125} />
					<Table.Column
						title="上傳狀態"
						dataIndex="flagA"
						width={125}
						render={(value) => {
							switch (value) {
								case 1:
									return '待轉'
								case 2:
									return '已轉'
								case 3:
									return '上傳成功'
								case 4:
									return '上傳失敗'
								case 5:
									return '完成'
								case 6:
									return '失敗'
								default:
									return value
							}
						}}
					/>
					<Table.Column
						title="是否作廢"
						dataIndex="flagCancel"
						width={125}
						render={(value) => {
							switch (value) {
								case 0:
									return '未作廢'
								case 1:
									return '已作廢'
								default:
									return value
							}
						}}
					/>
					<Table.Column
						title="是否折讓"
						dataIndex="hasAllowance"
						width={125}
						render={(value) => {
							if (value) {
								return '是'
							}
							return '否'
						}}
					/>
					<Table.Column
						title="發票明細"
						dataIndex="productItems"
						width={125}
						render={(value) => {
							return (
								<Button
									onClick={() => {
										setModalData(value)
										setModalVisible(true)
									}}
								>
									查看明細
								</Button>
							)
						}}
					/>
				</Table>
			</div>
		</div>
	)
}

export default InvoiceList
