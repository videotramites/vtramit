// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('VTramit', () => {
  before (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.visit(data.route)
    })
  })

  it('Intentem fer login amb un usuari incorrecte', () => {
    cy.get('#user').type('imh-01')
    cy.get('#password').type('abracadabrapatadecabra')
    cy.get('button[type="submit"]').click()
  })
  // TODO: no es comprova que hi hagi cap missatge d'error
})
