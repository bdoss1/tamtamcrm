import { Badge } from 'reactstrap'
import React from 'react'
import FormatMoney from '../common/FormatMoney'
import FormatDate from '../common/FormatDate'
import QuoteModel from '../models/QuoteModel'
import { translations } from '../utils/_translations'
import { quoteStatusColors } from '../utils/_colors'
import { quoteStatuses } from '../utils/_statuses'

export function getDefaultTableFields () {
    return [
        'number',
        'customer_id',
        'date',
        'due_date',
        'total',
        'balance',
        'status_id'
    ]
}

export default function QuotePresenter (props) {
    const { field, entity } = props

    const objQuoteModel = new QuoteModel(entity, props.customers)
    const is_late = objQuoteModel.isLate()
    const entity_status = is_late === true ? 100 : entity.status_id

    const status = (entity.deleted_at && !entity.is_deleted) ? (<Badge className="mr-2"
        color="warning">{translations.archived}</Badge>) : ((entity.deleted_at && entity.is_deleted) ? (
        <Badge className="mr-2" color="danger">{translations.deleted}</Badge>) : (
        <Badge color={quoteStatusColors[entity_status]}>{quoteStatuses[entity_status]}</Badge>))

    switch (field) {
        case 'assigned_to': {
            const assigned_user = JSON.parse(localStorage.getItem('users')).filter(user => user.id === parseInt(props.entity.assigned_to))
            return assigned_user.length ? `${assigned_user[0].first_name} ${assigned_user[0].last_name}` : ''
        }
        case 'user_id': {
            const user = JSON.parse(localStorage.getItem('users')).filter(user => user.id === parseInt(props.entity.user_id))
            return `${user[0].first_name} ${user[0].last_name}`
        }
        case 'balance':
        case 'total':
        case 'discount_total':
        case 'tax_total':
        case 'sub_total':
        case 'exchange_rate':
            return <FormatMoney customer_id={entity.customer_id} customers={props.customers} amount={entity[field]}/>
        case 'status_field':
            return status
        case 'date':
        case 'created_at':
        case 'due_date': {
            return <FormatDate field={field} date={entity[field]}/>
        }

        case 'status_id':
            return status

        case 'customer_id': {
            const index = props.customers.findIndex(customer => customer.id === entity[field])
            const customer = props.customers[index]
            return customer.name
        }

        case 'currency_id': {
            const currency = JSON.parse(localStorage.getItem('currencies')).filter(currency => currency.id === parseInt(props.entity.currency_id))
            return currency.length ? currency[0].iso_code : ''
        }

        default:
            return typeof entity[field] === 'object' ? JSON.stringify(entity[field]) : entity[field]
    }
}
