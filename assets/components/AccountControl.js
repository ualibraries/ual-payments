import React from 'react'
import ReactSVG from 'react-svg'
import PropTypes from 'prop-types'
import account from '../images/account.svg'

class HelpControl extends React.Component {
  handleButtonClick() {
    location.href =
      'https://arizona-primo.hosted.exlibrisgroup.com/primo-explore/account?vid=01UA&section=overview'
  }

  render() {
    return (
      <div className="header__control">
        <button
          className="header__control-button"
          onClick={this.handleButtonClick}
        >
          <div className="header__control-icon">
            <ReactSVG path={account} />
          </div>
          My account
        </button>
      </div>
    )
  }
}

export default HelpControl
