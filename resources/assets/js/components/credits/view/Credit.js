import React, { Component } from 'react'
import FileUploads from '../../documents/FileUploads'
import { Alert, Card, CardBody, CardHeader, Col, Nav, NavItem, NavLink, Row, TabContent, TabPane } from 'reactstrap'
import { translations } from '../../utils/_translations'
import CreditModel from '../../models/CreditModel'
import BottomNavigationButtons from '../../common/BottomNavigationButtons'
import Audit from '../../common/Audit'
import ViewContacts from '../../common/entityContainers/ViewContacts'
import Overview from './Overview'

export default class Credit extends Component {
    constructor (props) {
        super(props)
        this.state = {
            entity: this.props.entity,
            activeTab: '1',
            obj_url: null,
            show_success: false,
            file_count: this.props.entity.files.length || 0
        }

        this.creditModel = new CreditModel(this.state.entity)

        this.toggleTab = this.toggleTab.bind(this)
        this.loadPdf = this.loadPdf.bind(this)
        this.triggerAction = this.triggerAction.bind(this)
        this.refresh = this.refresh.bind(this)
    }

    refresh (entity) {
        this.creditModel = new CreditModel(entity)
        this.setState({ entity: entity })
    }

    triggerAction (action, is_add = false) {
        this.creditModel.completeAction(this.state.entity, action).then(response => {
            this.setState({ show_success: true }, () => {
                this.props.updateState(response, this.refresh, is_add)
            })

            setTimeout(
                function () {
                    this.setState({ show_success: false })
                }
                    .bind(this),
                2000
            )
        })
    }

    loadPdf () {
        this.creditModel.loadPdf().then(url => {
            console.log('url', url)
            this.setState({ obj_url: url }, () => URL.revokeObjectURL(url))
        })
    }

    toggleTab (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab }, () => {
                if (this.state.activeTab === '5') {
                    this.loadPdf()
                }
            })
        }
    }

    render () {
        const listClass = !Object.prototype.hasOwnProperty.call(localStorage, 'dark_theme') || (localStorage.getItem('dark_theme') && localStorage.getItem('dark_theme') === 'true') ? 'list-group-item-dark' : ''

        return (
            <React.Fragment>
                <Nav tabs className="nav-justified disable-scrollbars">
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('1')
                            }}
                        >
                            {translations.details}
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('2')
                            }}
                        >
                            {translations.contacts}
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '3' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('3')
                            }}
                        >
                            {translations.documents} ({this.state.file_count})
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '4' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('4')
                            }}
                        >
                            {translations.history}
                        </NavLink>
                    </NavItem>
                </Nav>
                <TabContent activeTab={this.state.activeTab}>
                    <TabPane tabId="1">
                        <Overview model={this.creditModel} entity={this.state.entity}
                            customers={this.props.customers}/>
                    </TabPane>

                    <TabPane tabId="2">
                        <Row>
                            <Col>
                                <ViewContacts entity={this.creditModel} customers={this.props.customers}/>
                            </Col>
                        </Row>
                    </TabPane>

                    <TabPane tabId="3">
                        <Row>
                            <Col>
                                <Card>
                                    <CardHeader>{translations.documents}</CardHeader>
                                    <CardBody>
                                        <FileUploads updateCount={(count) => {
                                            this.setState({ file_count: count })
                                        }} entity_type="Credit" entity={this.state.entity}
                                        user_id={this.state.entity.user_id}/>
                                    </CardBody>
                                </Card>
                            </Col>
                        </Row>
                    </TabPane>

                    <TabPane tabId="4">
                        <Row>
                            <Col>
                                <Audit entity="Credit" audits={this.state.entity.audits}/>
                            </Col>
                        </Row>
                    </TabPane>

                    <TabPane tabId="5">
                        <Row>
                            <Col>
                                <Card>
                                    <CardHeader>{translations.pdf}</CardHeader>
                                    <CardBody>
                                        <iframe style={{ width: '400px', height: '400px' }}
                                            className="embed-responsive-item" id="viewer" src={this.state.obj_url}/>
                                    </CardBody>
                                </Card>
                            </Col>
                        </Row>
                    </TabPane>
                </TabContent>

                {this.state.show_success &&
                <Alert color="primary">
                    {translations.action_completed}
                </Alert>
                }

                <BottomNavigationButtons button1_click={(e) => this.toggleTab('5')}
                    button1={{ label: translations.view_pdf }}
                    button2_click={(e) => this.triggerAction('clone_to_credit', true)}
                    button2={{ label: translations.clone_credit }}/>

            </React.Fragment>

        )
    }
}
