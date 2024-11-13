import { useState, useEffect } from 'react'
import { Form, Select, Button, DatePicker, Collapse, Divider } from 'antd'
import type { TimeRangePickerProps } from 'antd'
import dayjs from 'dayjs'
import { useGetOrders } from '@/hooks'
import { TFilter } from '../types'

const { RangePicker } = DatePicker
const { Option } = Select
const rangePresets: TimeRangePickerProps['presets'] = [
	{ label: 'Last 7 Days', value: [dayjs().add(-7, 'd'), dayjs()] },
	{ label: 'Last 14 Days', value: [dayjs().add(-14, 'd'), dayjs()] },
	{ label: 'Last 30 Days', value: [dayjs().add(-30, 'd'), dayjs()] },
	{ label: 'Last 90 Days', value: [dayjs().add(-90, 'd'), dayjs()] },
]
const index: React.FC<{ onFilter: (values: TFilter) => void , isOutLoading:boolean }> = ({
	onFilter,isOutLoading
}) => {
	const [form] = Form.useForm()
	// 取得訂單編號與發票號碼
	const { mutate, isLoading, meta } = useGetOrders({
		startDate: dayjs().add(-30, 'd').format('YYYY-MM-DD'),
		endDate: dayjs().format('YYYY-MM-DD'),
	})
	const [orderNos, setOrderNos] = useState(meta?.order_no || [])
	const [invoiceNo, setInvoiceNo] = useState(meta?.invoice_no || [])
	useEffect(() => {
		setOrderNos(meta?.order_no || [])
		setInvoiceNo(meta?.invoice_no || [])
	}, [meta])

	// 記錄當前選擇的是訂單編號還是發票號碼, 用於判斷是否要禁用另一個選項
	const [orderNosOrInvoiceNo, setOrderNosOrInvoiceNo] = useState<string>('')
	// 處理日期範圍變更重打AJAX獲取訂單,以及禁用訂單編號與發票號碼
	const handleValuesChange = (changedValues: TFilter, allValues: TFilter) => {

		if (changedValues.dateRange) {
			mutate(
				{
					action: 'get_orders_list',
					startDate: changedValues.dateRange[0].format('YYYY-MM-DD'),
					endDate: changedValues.dateRange[1].format('YYYY-MM-DD'),
				},
				{
					onSuccess: (data) => {
						setOrderNos(data.data.data.order_no)
						setInvoiceNo(data.data.data.invoice_no)
					},
					onError: (error) => {
						console.log(error)
					},
				},
			)
		}
		//orderNos 只能單筆
		if (changedValues.orderNos) {
			setOrderNosOrInvoiceNo('orderNos')
		} else {
			setOrderNosOrInvoiceNo('')
		}
		//invoiceNos 可以多筆
		if (changedValues.invoiceNos) {
			if (changedValues.invoiceNos.length > 0) {
				setOrderNosOrInvoiceNo('invoiceNos')
			} else {
				setOrderNosOrInvoiceNo('')
			}
		}
	}

	// 處理表單提交
	const handleOnFinish = (values: any) => {
		onFilter(values)
	}

	const children = (
		<div className="w-full relative">
			<div>
				<Form
					form={form}
					layout="vertical"
					onValuesChange={handleValuesChange}
					onFinish={handleOnFinish}
				>
					<div className="grid grid-cols-3 gap-6">
						<Form.Item
							label="日期範圍"
							name="dateRange"
							initialValue={[dayjs().add(-30, 'd'), dayjs()]}
						>
							<RangePicker presets={rangePresets} className="w-full" />
						</Form.Item>
						<Form.Item label="訂單編號" name="orderNos">
							<Select
								disabled={isLoading || orderNosOrInvoiceNo === 'invoiceNos'}
								loading={isLoading}
								allowClear={true}
							>
								{orderNos?.map((item) => (
									<Option key={item} value={item}>
										{item}
									</Option>
								))}
							</Select>
						</Form.Item>
						<Form.Item label="發票號碼" name="invoiceNos">
							<Select
								dropdownRender={(menu) => (
									<>
										<div className="flex justify-between p-2">
											<Button
												type="link"
												onClick={() =>
													form.setFieldsValue({ orderNos: orderNos })
												}
											>
												全選
											</Button>
											<Button
												type="link"
												onClick={() => {
													form.setFieldsValue({ orderNos: [] })
													setOrderNosOrInvoiceNo('')
												}}
											>
												清除選項
											</Button>
										</div>
										<Divider style={{ margin: '4px 0' }} />
										{menu}
									</>
								)}
								disabled={isLoading || orderNosOrInvoiceNo === 'orderNos'}
								loading={isLoading}
								mode="multiple"
							>
								{invoiceNo?.map((item) => (
									<Option key={item} value={item}>
										{item}
									</Option>
								))}
							</Select>
						</Form.Item>
					</div>
					<Form.Item className="mt-6">
						<Button type="primary" htmlType="submit" className="w-full" loading={isOutLoading}>
							查詢
						</Button>
					</Form.Item>
				</Form>
			</div>
		</div>
	)

	return (
		<Collapse
			bordered={false}
			className="bg-white"
			defaultActiveKey={['filters']}
			items={[
				{
					key: 'filters',
					label: (
						<span className="font-semibold text-base relative -top-0.5">
							查詢條件
						</span>
					),
					children,
				},
			]}
		/>
	)
}
export default index
