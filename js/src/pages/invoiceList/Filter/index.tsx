import { useState, useEffect } from 'react'
import { Form, Select, Button, DatePicker, Collapse } from 'antd'
import type { TimeRangePickerProps } from 'antd'
import dayjs from 'dayjs'
import { useGetOrders } from '@/hooks'

const { RangePicker } = DatePicker
const { Option } = Select
const rangePresets: TimeRangePickerProps['presets'] = [
	{ label: 'Last 7 Days', value: [dayjs().add(-7, 'd'), dayjs()] },
	{ label: 'Last 14 Days', value: [dayjs().add(-14, 'd'), dayjs()] },
	{ label: 'Last 30 Days', value: [dayjs().add(-30, 'd'), dayjs()] },
	{ label: 'Last 90 Days', value: [dayjs().add(-90, 'd'), dayjs()] },
]
const index: React.FC = () => {
	const [form] = Form.useForm()
	const { mutate, isLoading, meta } = useGetOrders({
		startDate: dayjs().add(-30, 'd').format('YYYY-MM-DD'),
		endDate: dayjs().format('YYYY-MM-DD'),
	})
	const [orderNo, setOrderNo] = useState(meta?.order_no || [])
	const [invoiceNo, setInvoiceNo] = useState(meta?.invoice_no || [])
	useEffect(() => {
		setOrderNo(meta?.order_no || [])
		setInvoiceNo(meta?.invoice_no || [])
	}, [meta])
	const handleValuesChange = (changedValues: any, allValues: any) => {
		if (changedValues.dateRange) {
			mutate(
				{
					action: 'get_orders_list',
					startDate: changedValues.dateRange[0].format('YYYY-MM-DD'),
					endDate: changedValues.dateRange[1].format('YYYY-MM-DD'),
				},
				{
					onSuccess: (data) => {
						setOrderNo(data.data.data.order_no)
						setInvoiceNo(data.data.data.invoice_no)
					},
					onError: (error) => {
						console.log(error)
					},
				},
			)
		}
	}


	const children = (
		<div className="w-full relative">
			<div>
				<Form layout="vertical" onValuesChange={handleValuesChange}>
					<div className="grid grid-cols-3 gap-6">
						<Form.Item
							label="日期範圍"
							name="dateRange"
							initialValue={[dayjs().add(-30, 'd'), dayjs()]}
						>
							<RangePicker presets={rangePresets} className="w-full" />
						</Form.Item>
						<Form.Item label="訂單編號" name="orderNo">
							<Select loading={isLoading}>
								{orderNo?.map((item) => (
									<Option key={item} value={item}>
										{item}
									</Option>
								))}
							</Select>
						</Form.Item>
						<Form.Item label="發票號碼" name="invoiceNo">
							<Select loading={isLoading}>
								{invoiceNo?.map((item) => (
									<Option key={item} value={item}>
										{item}
									</Option>
								))}
							</Select>
						</Form.Item>
					</div>
					<Form.Item className="mt-6">
						<Button type="primary" htmlType="submit" className="w-full">
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
