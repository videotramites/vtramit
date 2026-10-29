// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('Procedures view', () => {
  before(() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })

  it("Creem una cita i l'esborrem", () =>{
    // Creem una cita
    cy.crearCita()
    // Esperem i estem a editar cita

    // Despleguem el menú de puntets
    cy.get('.icon > .material-design-icon').click()
    cy.get('[data-test="delete"]').click()
    cy.get('.toast-success').should('be.visible')
    cy.get('.toast-success', { timeout: 20000 }).should('not.exist')
  }) 
  // TODO: Hauríem d'afegir alguna confirmació de toast o algo al Vtramit
})