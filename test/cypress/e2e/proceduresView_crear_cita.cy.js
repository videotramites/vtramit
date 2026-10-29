// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
  before (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })

  it("Hauria de poder crear una cita vàlida correctament", () =>{
    // Creem una cita
    cy.crearCita()
    // Esperem i estem a editar cita
  })
})
