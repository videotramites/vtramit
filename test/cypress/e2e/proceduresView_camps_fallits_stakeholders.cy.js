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
    cy.procedure('468496',0,0,0,'topic','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
    cy.stakeholder(0,'', 'prova', '4539746','prova@prova.com')
    cy.get('[data-test="save"]').click()

    cy.get('[data-test="id"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  it("Hauria de donar error per camp nom buit", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure('468496',0,0,0,'topic','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
    cy.stakeholder(0,'4564568', '', '4539746','prova@prova.com')
    cy.get('[data-test="save"]').click()

    cy.get('[data-test="name"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  // it("Hauria de donar error per camp phone buit", () =>{
  //   cy.get('[data-test="new"]').should('be.visible').click()
  //   cy.get('[data-test="procedure"]').select(0)
  //   cy.procedure('468496',0,0,0,'topic','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
  //   cy.stakeholder(0,'4564568', 'prova', '','prova@prova.com')
  //   cy.get('[data-test="save"]').click()

  //   cy.get('[data-test="phone"]')
  //     .parent()
  //     .next('.error_message')
  //     .should('not.be.empty')
  // })

  it("Hauria de donar error per camp email buit", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure('468496',0,0,0,'topic','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
    cy.stakeholder(0,'4564568', 'prova', '4539746','')
    cy.get('[data-test="save"]').click()

    cy.get('[data-test="email"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })

  it("Hauria de donar error per camp email incorrecte", () =>{
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure('468496',0,0,0,'topic','2025-09-30 12:25','2025-09-30 13:25','comentari prova')
    cy.stakeholder(0,'4564568', 'prova', '4539746','prova@prova')
    cy.get('[data-test="save"]').click()

    cy.get('[data-test="email"]')
      .parent()
      .next('.error_message')
      .should('not.be.empty')
  })
})
