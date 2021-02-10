import React, { Component } from 'react'
import axios from 'axios'
import AddToken from './edit/AddToken'
import { Alert, Card, CardBody, Row } from 'reactstrap'
import DataTable from '../common/DataTable'
import TokenFilters from './TokenFilters'
import TokenItem from './TokenItem'
import Snackbar from '@material-ui/core/Snackbar'
import { translations } from '../utils/_translations'
import { getDefaultTableFields } from '../presenters/TokenPresenter'
import SubscriptionFilters from "../subscriptions/SubscriptionFilters";

export default class Tokens extends Component {
    constructor (props) {
        super(props)

        this.state = {
            currentPage: 1,
             totalPages: null,
             pageLimit: !localStorage.getItem('number_of_rows') ? Math.ceil(window.innerHeight / 90) : localStorage.getItem('number_of_rows'),
             currentInvoices: [],
            isOpen: window.innerWidth > 670,
            error: '',
            show_success: false,
            error_message: translations.unexpected_error,
            success_message: translations.success_message,
            dropdownButtonActions: ['download'],
            tokens: [],
            users: [],
            cachedData: [],
            view: {
                ignore: [],
                viewMode: false,
                viewedId: null,
                title: null
            },
            errors: [],
            filters: {
                searchText: '',
                status: 'active',
                start_date: '',
                end_date: ''
            }
        }

        this.addUserToState = this.addUserToState.bind(this)
        this.userList = this.userList.bind(this)
        this.filterTokens = this.filterTokens.bind(this)
        this.getUsers = this.getUsers.bind(this)
    }

    componentDidMount () {
        this.getUsers()
    }

    addUserToState (tokens) {
        const cachedData = !this.state.cachedData.length ? tokens : this.state.cachedData
        this.setState({
            tokens: tokens,
            cachedData: cachedData
        })
    }

    onPageChanged(data) {
         let { tokens, pageLimit } = this.state
         const { currentPage, totalPages } = data

         if (data.invoices) {
             tokens = data.invoices
         }

         const offset = (currentPage - 1) * pageLimit
         const currentInvoices = tokens.slice(offset, offset + pageLimit)

         this.setState({ currentPage, currentInvoices, totalPages })
     }

    filterTokens (filters) {
        this.setState({ filters: filters })
    }

    resetFilters () {
        this.props.reset()
    }

    userList (props) {
        const { tokens, users } = this.state
        return <TokenItem showCheckboxes={props.showCheckboxes} tokens={tokens} users={users}
            viewId={props.viewId}
            show_list={props.show_list}
            ignoredColumns={props.default_columns} addUserToState={this.addUserToState}
            toggleViewedEntity={props.toggleViewedEntity}
            bulk={props.bulk}
            onChangeBulk={props.onChangeBulk}/>
    }

    getUsers () {
        axios.get('api/users')
            .then((r) => {
                this.setState({
                    users: r.data
                })
            })
            .catch((e) => {
                this.setState({
                    loading: false,
                    error: e
                })
            })
    }

    handleClose () {
        this.setState({ error: '', show_success: false })
    }

    setFilterOpen (isOpen) {
        this.setState({ isOpen: isOpen })
    }

    setError (message = null) {
        this.setState({ error: true, error_message: message === null ? translations.unexpected_error : message })
    }

    setSuccess (message = null) {
        this.setState({
            show_success: true,
            success_message: message === null ? translations.success_message : message
        })
    }

    render () {
        const { searchText, status, start_date, end_date } = this.state.filters
        const { view, tokens, error, isOpen, error_message, success_message, show_success } = this.state
        const fetchUrl = `/api/tokens?search_term=${searchText}&status=${status}&start_date=${start_date}&end_date=${end_date} `
        const margin_class = isOpen === false || (Object.prototype.hasOwnProperty.call(localStorage, 'datatable_collapsed') && localStorage.getItem('datatable_collapsed') === true)
            ? 'fixed-margin-datatable-collapsed'
            : 'fixed-margin-datatable fixed-margin-datatable-mobile'

        return (
            <Row>
                <div className="col-12">
                    <div className="topbar">
                        <Card>
                            <CardBody>
                                <TokenFilters
                                    pageLimit={pageLimit}
                                     cachedData={this.state.cachedData}
                                     updateList={this.onPageChanged.bind(this)}
                                     setFilterOpen={this.setFilterOpen.bind(this)} tokens={tokens}
                                    updateIgnoredColumns={this.updateIgnoredColumns}
                                    filters={this.state.filters} filter={this.filterTokens}
                                    saveBulk={this.saveBulk} ignoredColumns={this.state.ignoredColumns}/>

                                <AddToken
                                    tokens={tokens}
                                    action={this.addUserToState}
                                />
                            </CardBody>
                        </Card>
                    </div>

                    {error &&
                    <Snackbar open={error} autoHideDuration={3000} onClose={this.handleClose.bind(this)}>
                        <Alert severity="danger">
                            {error_message}
                        </Alert>
                    </Snackbar>
                    }

                    {show_success &&
                    <Snackbar open={show_success} autoHideDuration={3000} onClose={this.handleClose.bind(this)}>
                        <Alert severity="success">
                            {success_message}
                        </Alert>
                    </Snackbar>
                    }

                    <div className={margin_class}>
                        <Card>
                            <CardBody>
                                <DataTable

pageLimit={pageLimit}
                                     onPageChanged={this.onPageChanged.bind(this)}
                                     currentData={currentInvoices}
                                     hide_pagination={true}

                                    default_columns={getDefaultTableFields()}
                                    setSuccess={this.setSuccess.bind(this)}
                                    setError={this.setError.bind(this)}
                                    dropdownButtonActions={this.state.dropdownButtonActions}
                                    entity_type="Token"
                                    bulk_save_url="/api/tokens/bulk"
                                    view={view}
                                    userList={this.userList}
                                    fetchUrl={fetchUrl}
                                    updateState={this.addUserToState}
                                />
                            </CardBody>
                        </Card>
                    </div>
                </div>
            </Row>
        )
    }
}
