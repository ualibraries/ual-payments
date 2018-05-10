import forEach from 'lodash.foreach'
import currency from 'currency.js'

function totalAmount() {
  let total = 0.0

  forEach(
    document.getElementsByClassName('charges__item-checkbox-input'),
    element => {
      total += Number.parseFloat(element.dataset.feeBalance)
    }
  )

  document.getElementById('totalAmount').innerHTML = `$${currency(total)}`
}

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

  document.getElementById('totalSelectedAmount').innerHTML = `$${currency(total)}`
}

document.addEventListener('DOMContentLoaded', () => {
  totalAmount()

  selectedTotal()

  forEach(
    document.getElementsByClassName('charges__item-checkbox-input'),
    element => {
      element.addEventListener('click', selectedTotal)
    }
  )
})
