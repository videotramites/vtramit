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

  it("Hauria de donar error per camp id buit", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure('',0,0,0,'topic','2025-09-20 12:25','2025-09-20 13:25','comentari prova')
    
    cy.stakeholder(0,'1111', 'prova', '4539746','prova@prova.com')
    cy.get('[data-test="save"]').click()

    cy.get('[data-test="cita-id"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  // TODO: no, no dona error si topic és buit
  // it("Hauria de donar error per camp topic buit", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('4964894',0,0,0,'','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
    
  //   cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('[data-test="topic"]')
  //     .parent()
  //     .next('.error_message')
  //     .should('not.be.empty')
  // })

  // TODO: no, no dona error si la data és buida
  // it("Hauria de donar error per camp data buida", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('4964894',0,0,0,'topic','','2025-09-30 13:25','comentari prova')

  //   cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('[data-test="date"]')
  //     .parent()
  //     .next('.error_message')
  //     .should('not.be.empty')
  // })

  it("Hauria de donar error per camp data massa antiga", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure('4964894',0,0,0,'topic','2024-09-30 13:25','','comentari prova')

    cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
    cy.get('[data-test="save"]').click()

    cy.get('.toast-error').should('be.visible')
  })

  // TODO: no, no dona error si la data de fi és buida
  // it("hauria de donar error per camp data de fi buida", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('4964894',0,0,0,'topic','2025-09-30 12:25','','comentari prova')
    
  //   cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('[data-test="deadLine"]')
  //     .parent()
  //     .next('.error_message')
  //     .should('not.be.empty')
  // })

  // TODO: no, no dona error si la data de fi és massa antiga
  // it("hauria de donar error per camp data de fi massa antiga", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('4964894',0,0,0,'topic','','2024-09-30 13:25','comentari prova')
    
  //   cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('.toast-error').should('be.visible')
  // })

  // TODO: no, no dona error si el comentari és buit
  // it("hauria de donar error per camp comentari buit", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('4964894',0,0,0,'','2025-09-30 12:25','2025-09-30 13:25','')
    
  //   cy.stakeholder(0,'4894f', 'prova', '4539746','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('[data-test="comment"]')
  //     .parent()
  //     .next('.error_message')
  //     .should('not.be.empty')
  // })
})
