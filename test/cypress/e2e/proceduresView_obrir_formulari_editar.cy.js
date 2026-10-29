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

  // TODO: Aquests tests realment no comproven que la funcionalitat estigui bé, de fet només fan clic i ja.
  it("Hauria de poder crear una cita vàlida correctament i obrir el formulari d'editar", () =>{
    // Creem una cita
    cy.crearCita()
    // Esperem i estem a editar cita

    cy.reload()
    cy.get('[data-test^="id"]').last().click()
  })
  // TODO: Hauríem d'anar al llistat principal (perquè ara estem en mode edició i el formulari ja està obert), fer click a un element i llavors sí mirar si s'ha obert el formulari d'edició.
})
