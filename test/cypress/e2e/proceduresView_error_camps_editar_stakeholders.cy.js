// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
  beforeEach(() => {
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
  // TODO: Aquests tests no es poden estructurar així: si s'executessin en paral·lel no tindrien les dades necessaries disponibles.
  it("Hauria de donar error per camp id del stakeholder incorrecte", () =>{
    cy.get('.procedures-table').then($body => {
      if ($body.find('.last').length) {
        cy.get('.last').click()
      }
    })

    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="id-edit"]').clear()
    cy.get('[data-test="save"]').click()
    cy.get('[data-test="id-edit"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  it("Hauria de donar error per camp nom del stakeholder incorrecte", () =>{
    cy.crearCita()
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="name-edit"]').clear()
    cy.get('[data-test="save"]').click()
    cy.get('[data-test="name-edit"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  it("Hauria de donar error per camp telefon del stakeholder incorrecte", () =>{
    cy.crearCita()
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="phone-edit"]').clear()
    cy.get('[data-test="save"]').click()
  })

  it("Hauria de donar error per camp email del stakeholder incorrecte", () =>{
    cy.crearCita()
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="email-edit"] ').clear()
    cy.get('[data-test="save"]').click()
    cy.get('[data-test="email-edit"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
    
  })
})
