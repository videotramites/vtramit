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

  it("Creem una cita amb més d'un stakeholder, comprovem que puguem afegir i treure stakeholders", () =>{
    const start = Cypress.env('visitStart');
    const end = Cypress.env('visitEnd');

    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)

    cy.get('[data-test="cita-id"]').type('000111')
    cy.get('[data-test="department"]').select(0)
    cy.get('[data-test="user"]').select(0)
    cy.get('[data-test="channel"]').select(0)
    cy.get('[data-test="topic"]').type('topic')
    cy.get('[data-test="date"]').type(start)
    cy.get('[data-test="deadline"]').type(end)
    cy.get('[data-test="comment"]').type('comentari prova')

    cy.stakeholder(0,'4894684f', 'prova', '4539746','prova@prova.com')
    cy.get('[data-test="add-stakeholder"]').click()
    cy.stakeholder(1,'4894684f', 'prova2', '435135151','prova2@prova.com')
    cy.get('[data-test="add-stakeholder"]').click()
    cy.stakeholder(2,'9856489b', 'prova3', '489498498','prova3@prova.com')
    
    cy.removeStakeholder(1)
})
// TODO: aquí no s'està realment comprovant que l'acció funciona, només s'està fent click a botons i prou.
})
