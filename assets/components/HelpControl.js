import React from 'react'
import PropTypes from 'prop-types'
import askusIcon from '../images/ask-us.svg'

class HelpControl extends React.Component {
  // Load LibChat script
  componentDidMount() {
    let e = document.createElement('script')
    e.src =
      '//v2.libanswers.com/load_chat.php?hash=07713bc057f66ebcdccd4dd1b4a2be3e'
    document.body.appendChild(e)
  }

  render() {
    return (
      <div
        className={
          this.props.open ? 'header__control--active' : 'header__control'
        }
      >
        <button className="header__control-button" onClick={this.props.handler}>
          <div
            className="header__control-icon"
            dangerouslySetInnerHTML={{ __html: askusIcon }}
          />
          Ask us
        </button>

        <div className="header__control-popout" style={{ width: '12rem' }}>
          <div className="header__popout-setting">
            Text: <a href="tel:5207627271">(520) 762-7271</a>
          </div>
          <div className="header__popout-setting">
            Phone: <a href="tel:5206216442">(520) 621-6442</a>
          </div>
          <div className="header__popout-setting">
            Email: <a href="mailto:library@arizona.edu">library@arizona.edu</a>
          </div>
          <div className="header__popout-setting">
            <div id="libchat_07713bc057f66ebcdccd4dd1b4a2be3e" />
          </div>
          <div className="header__popout-setting">
            <a href="http://new.library.arizona.edu/contact">
              More contact information
            </a>
          </div>
        </div>
      </div>
    )
  }
}

export default HelpControl
