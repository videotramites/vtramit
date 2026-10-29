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

  it("Creem una cita amb data massa del passat i dona error", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('select[data-test="procedure"] option').should('exist')
    cy.get('[data-test="procedure"]').select(0)
  
    cy.procedure('000112',0,0,0,'topic','2025-09-20 12:25','2025-09-20 13:25','comentari prova')
    cy.stakeholder(0,'4894684f', 'prova', '4539746','prova@prova.com')
    cy.get('[data-test="save"]').click()

    cy.get('.toast-error').should('be.visible')
  })

  // TODO: Realment si que deixa, està bé?
  // it("Creem una cita amb data massa del futur i dona error", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  
  //   cy.procedure('000112',0,0,0,'topic','3025-09-20 12:25','3025-09-20 13:25','comentari prova')
    
  //   cy.stakeholder(0,'4894684f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('.toast-error').should('be.visible')
  // })
})
