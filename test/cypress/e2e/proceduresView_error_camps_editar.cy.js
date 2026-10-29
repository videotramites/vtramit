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
  it("Hauria de donar error per camp id incorrecte", () =>{
    cy.crearCita()
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="cita-id"]').clear()
    cy.get('[data-test="save"]').click()
    cy.get('[data-test="cita-id"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })
/*
  it("Hauria de donar error per camp topic incorrecte", () =>{
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="topic"] input').clear()
    cy.get('[data-test="save"]').click()
    cy.get('[data-test="cita-id"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })
*/
  it("Hauria de donar error per camp data incorrecte", () =>{
    cy.crearCita()
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="date"] input').type('2024-09-30 13:25')
    cy.get('[data-test="save"]').click()
    cy.get('.toast-error').should('be.visible')
  })
/*
  it("Hauria de donar error per camp data incorrecte", () =>{
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="deadline"] input').clear()
    cy.get('[data-test="save"]').click()
  })
*/
/*
  it("Hauria de donar error per camp comentari incorrecte", () =>{
    cy.get('[data-test^="id"]').last().click()
    cy.get('[data-test="comment"]').clear()
    cy.get('[data-test="save"]').click()
  })
    */
})
