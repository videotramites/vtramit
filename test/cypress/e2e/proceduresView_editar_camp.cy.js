// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
  beforeEach (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })

  it("Creem una cita i editem un camp correctament.", () =>{
    // Creem una cita
    cy.crearCita()
    // Esperem i estem a editar cita

    cy.get('[data-test="cita-id"]').clear().type("0006651")
    cy.get('[data-test="save"]').click()

    // Esperem que el toast de success s'hagi amagat del tot
    cy.get('.toast-success').should('be.visible')
    cy.get('.toast-success', { timeout: 20000 }).should('not.exist')
   })
})