import { Modal, Table } from 'antd'
import {TModalProps} from '../types'

const index: React.FC<TModalProps> = ({ visible, setVisible, dataSource }) => {
	return (
		<Modal className='min-w-[800px]' title="發票明細" open={visible} onOk={()=>setVisible(false)} onCancel={()=>setVisible(false)}>
			<Table dataSource={dataSource} >
				<Table.Column title="商品名稱" dataIndex="description" width={125} />
				<Table.Column title="商品數量" dataIndex="quantity" width={75} />
				<Table.Column title="商品單位" dataIndex="unit" width={75} />
				<Table.Column title="商品單價" dataIndex="unitPrice" width={75} />
				<Table.Column title="商品小計" dataIndex="amount" width={75} />
				<Table.Column title="商品備註" dataIndex="remark" width={125} />
				<Table.Column title="商品稅別" dataIndex="taxType" width={75} />
			</Table>
		</Modal>
	)
}
export default index
