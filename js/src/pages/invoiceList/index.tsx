import '@/assets/scss/index.scss'
import { Table } from 'antd'
import Filter from './Filter'

const InvoiceList: React.FC = () => {
	return (
		<div className="w-full relative">
			<h1>發票查詢</h1>
			<div className="pr-5 flex flex-col gap-10">
				<Filter />
				<Table>
					<Table.Column title="開立日期" dataIndex="CRT_DAT" width={125} />
					<Table.Column title="發票號碼" dataIndex="invoiceNumber" width={125} />
					<Table.Column title="買受人統編" dataIndex="buyerIdentifier" width={125} />
					<Table.Column title="買受人名稱" dataIndex="buyerName" width={125} />
					<Table.Column title="發票金額" dataIndex="totalAmount" width={125} />
					<Table.Column title="訂單編號" dataIndex="orderNo" width={125} />
					<Table.Column title="上傳狀態" dataIndex="flagA" width={125} />
					<Table.Column title="是否作廢" dataIndex="flagCancel" width={125} />
					<Table.Column title="是否折讓" dataIndex="hasAllowance" width={125} />
					<Table.Column title="發票明細" dataIndex="productItems" width={125} />
				</Table>
			</div>
		</div>
	)
}

export default InvoiceList
