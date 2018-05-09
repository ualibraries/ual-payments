import React from 'react'
import ReactSVG from 'react-svg'
import HelpControl from './HelpControl'
import branding from '../images/branding.svg'

class Header extends React.Component {
  state = {
    helpControlActive: false
  }

  /**
   * Handle a click event on the help control.
   */
  helpControlHandler = () => {
    this.setState({
      helpControlActive: !this.state.helpControlActive,
      settingsControlActive: false
    })
  }

  /**
   * Handler closes help and settings control.
   */
  globalExitHandler = event => {
    let resetState = () => {
      this.setState({
        helpControlActive: false
      })
    }

    !document.getElementById('header__uicontrols').contains(event.target)
      ? resetState()
      : null
  }

  /**
   * Bind the global exit handler after the component mounts.
   */
  componentDidMount() {
    document.body.addEventListener('click', this.globalExitHandler)
  }

  /**
   * Render component.
   */
  render() {
    return (
      <div className="Header">
        <div className="header__inner">
          <a
            href="https://new.library.arizona.edu"
            className="logo"
          >
            <ReactSVG path={branding} />
          </a>
          <div className="header__uicontrols-outer">
            <div className="header__uicontrols" id="header__uicontrols">
              <HelpControl
                handler={this.helpControlHandler}
                open={this.state.helpControlActive}
              />
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default Header
