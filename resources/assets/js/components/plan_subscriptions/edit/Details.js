import React, { Component } from 'react'
import { FormGroup, Input, Label } from 'reactstrap'
import { translations } from '../../utils/_translations'
import PlanDropdown from '../../common/dropdowns/PlanDropdown'

export default class Details extends Component {
    render () {
        return (
            <React.Fragment>
                <FormGroup className="mb-3">
                    <Label>{translations.name}</Label>
                    <Input className={this.props.hasErrorFor('name') ? 'is-invalid' : ''} type="text" name="name"
                        value={this.props.plan.name} onChange={this.props.handleInput}/>
                    {this.props.renderErrorFor('name')}
                </FormGroup>

                <FormGroup className="mb-3">
                    <Label>{translations.number_of_licences}</Label>
                    <Input className={this.props.hasErrorFor('number_of_licences') ? 'is-invalid' : ''} type="text" name="number_of_licences"
                        value={this.props.plan.number_of_licences} onChange={this.props.handleInput}/>
                    {this.props.renderErrorFor('number_of_licences')}
                </FormGroup>

                <FormGroup className="mb-3">
                    <Label>{translations.plan}</Label>
                    <PlanDropdown handleInputChanges={this.props.handleInput} plan={this.props.plan.plan_id}
                        plans={this.props.plan_types}/>
                    {this.props.renderErrorFor('name')}
                </FormGroup>
            </React.Fragment>
        )
    }
}
