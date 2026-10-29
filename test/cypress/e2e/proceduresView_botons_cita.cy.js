// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
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

  it("Creem cita i mirem si hi ha disponibles els botons per anar a pujada i baixada de fitxers i videoconferència", () =>{
    // Creem una cita
    cy.crearCita()
    // Esperem i estem a editar cita

    cy.get('[data-test^="downloads"]').last().should('exist').click()
    cy.get('[data-test^="uploads"]').last().should('exist').click()
    cy.get('[data-test^="videoconference"] ').last().should('exist').click()
  })
  // TODO: mirar si ha carregat els tabs amb la tècnica de canviar el target
})