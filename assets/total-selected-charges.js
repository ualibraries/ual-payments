import forEach from 'lodash.foreach'
import currency from 'currency.js'

function selectedTotal() {
  let total = 0.0

  forEach(
    document.getElementsByClassName('charges__item-checkbox-input'),
    element => {
      if (element.checked) {
        total += Number.parseFloat(element.dataset.feeBalance)
      }
    }
  )

  document.getElementById('totalSelectedAmount').innerHTML = `$${currency(
    total
  )}`

  if (total > 0) {
    document.getElementById('submitButton').removeAttribute('disabled')
  } else {
    document.getElementById('submitButton').setAttribute('disabled', 'disabled')
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (!document.getElementsByClassName('charges__item-checkbox-input').length) {
    return
  }

  selectedTotal()

  forEach(
    document.getElementsByClassName('charges__item-checkbox-input'),
    element => {
      element.addEventListener('click', selectedTotal)
    }
  )
})
