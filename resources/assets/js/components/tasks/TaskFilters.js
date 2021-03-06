import React, { Component } from 'react'
import { Button, Col, FormGroup, Row } from 'reactstrap'
import TableSearch from '../common/TableSearch'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import FilterTile from '../common/FilterTile'
import UserDropdown from '../common/dropdowns/UserDropdown'
import CustomerDropdown from '../common/dropdowns/CustomerDropdown'
import TaskStatusDropdown from '../common/dropdowns/TaskStatusDropdown'
import StatusDropdown from '../common/StatusDropdown'
import ProjectDropdown from '../common/dropdowns/ProjectDropdown'
import filterSearchResults, { filterStatuses } from '../utils/_search'

export default class TaskFilters extends Component {
    constructor (props) {
        super(props)

        this.state = {
            isOpen: false,
            dropdownButtonActions: ['download'],
            filters: {
                start_date: '',
                end_date: '',
                project_id: '',
                status_id: 'active',
                task_status_id: '',
                user_id: '',
                customer_id: '',
                task_type: '',
                searchText: ''
            }
        }

        this.filterTasks = this.filterTasks.bind(this)
        this.getFilters = this.getFilters.bind(this)
    }

    setFilterOpen (isOpen) {
        this.setState({ isOpen: isOpen })
    }

    filterTasks (event) {
        if ('start_date' in event) {
            this.setState(prevState => ({
                filters: {
                    ...prevState.filters,
                    start_date: event.start_date,
                    end_date: event.end_date
                }
            }), () => this.props.filter(this.state.filters))
            return
        }

        const column = event.target.id
        const value = event.target.value

        if (value === 'all') {
            const updatedRowState = this.state.filters.filter(filter => filter.column !== column)
            this.setState({ filters: updatedRowState }, () => this.props.filter(this.state.filters))
            return true
        }

        this.setState(prevState => ({
            filters: {
                ...prevState.filters,
                [column]: value
            }
        }), () => this.props.filter(this.state.filters))

        return true
    }

    getFilters () {
        const { searchText, start_date, end_date, customer_id, project_id, task_status_id, task_type, user_id } = this.state.filters

        return (

            <Row form>
                <Col md={2}>
                    <TableSearch onChange={(e) => {
                        const myArrayFiltered = filterSearchResults(e.target.value, this.props.cachedData, this.props.customers)
                        this.props.updateList(myArrayFiltered || [], false, this.state.filters)
                    }}/>
                </Col>

                <Col md={3}>
                    <CustomerDropdown
                        customers={this.props.customers}
                        customer={this.props.filters.customer_id}
                        handleInputChanges={(e) => {
                            this.setState(prevState => ({
                                filters: {
                                    ...prevState.filters,
                                    [e.target.id]: e.target.value
                                }
                            }), () => {
                                const results = filterStatuses(this.props.cachedData, e.target.value, this.state.filters)
                                this.props.updateList(results || [], false, this.state.filters)
                            })
                        }}
                        name="customer_id"
                    />
                </Col>

                <Col sm={12} md={3} className="mt-3 mt-md-0">
                    <UserDropdown
                        handleInputChanges={(e) => {
                            const name = e.target.name
                            const value = e.target.value
                            this.setState(prevState => ({
                                filters: {
                                    ...prevState.filters,
                                    [name]: value
                                }
                            }), () => {
                                const results = filterStatuses(this.props.cachedData, value, this.state.filters)
                                this.props.updateList(results || [], false, this.state.filters)
                            })
                        }}
                        users={this.props.users}
                        name="user_id"
                    />
                </Col>

                <Col sm={12} md={2} className="mt-3 mt-md-0">

                    <TaskStatusDropdown
                        task_type={1}
                        handleInputChanges={(e) => {
                            const name = e.target.name
                            const value = e.target.value
                            this.setState(prevState => ({
                                filters: {
                                    ...prevState.filters,
                                    [name]: value
                                }
                            }), () => {
                                const results = filterStatuses(this.props.cachedData, value, this.state.filters)
                                this.props.updateList(results || [], false, this.state.filters)
                            })
                        }}
                    />
                </Col>

                <Col sm={12} md={2} className="mt-3 mt-md-0">
                    <FormGroup>
                        <StatusDropdown filterStatus={(e) => {
                            this.setState(prevState => ({
                                filters: {
                                    ...prevState.filters,
                                    [e.target.id]: e.target.value
                                }
                            }), () => {
                                const results = filterStatuses(this.props.cachedData, e.target.value, this.state.filters)
                                this.props.updateList(results || [], false, this.state.filters)
                            })
                        }}/>
                    </FormGroup>
                </Col>

                <Col sm={12} md={1} className="mt-3 mt-md-0">
                    <CsvImporter customers={this.props.customers} filename="tasks.csv"
                        url={`/api/tasks?search_term=${searchText}&project_id=${project_id}&task_status=${task_status_id}&task_type=${task_type}&customer_id=${customer_id}&user_id=${user_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col sm={12} md={3} className="mt-3 mt-md-0">
                    <ProjectDropdown
                        handleInputChanges={(e) => {
                            const name = e.target.name
                            const value = e.target.value
                            this.setState(prevState => ({
                                filters: {
                                    ...prevState.filters,
                                    [name]: value
                                }
                            }), () => {
                                const results = filterStatuses(this.props.cachedData, value, this.state.filters)
                                this.props.updateList(results || [], false, this.state.filters)
                            })
                        }}
                        name="project_id"
                    />
                </Col>

                <Col sm={12} md={2} className="mt-3 mt-md-0">
                    <FormGroup>
                        <DateFilter onChange={this.filterTasks}/>
                    </FormGroup>
                </Col>
                <Col sm={12} md={1} className="mt-3 mt-md-0">
                    <Button color="primary" onClick={() => {
                        location.href = '/#/kanban?type=task'
                    }}>Kanban view </Button>
                </Col>
            </Row>
        )
    }

    render () {
        const filters = this.getFilters()

        return (<FilterTile setFilterOpen={this.props.setFilterOpen} filters={filters}/>)
    }
}
