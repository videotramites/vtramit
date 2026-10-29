// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })




Cypress.Commands.add('login', (username, password) => {
  cy.get('#user').type(username)
  cy.get('#password').type(password)
  cy.get('button[type="submit"]').click()
})

Cypress.Commands.add('procedure', (procedureId, department, user, channel, topic, initialDate, deadline, comment) => {
  if (procedureId !== undefined) {
    cy.get('[data-test="cita-id"]').clear()
    if (procedureId !== '') cy.get('[data-test="cita-id"]').type(procedureId)
  }
  
  cy.get('[data-test="department"]').select(department)

  cy.get('[data-test="user"]').select(user)

  cy.get('[data-test="channel"]').select(channel)
    
  if (topic !== undefined) {
    cy.get('[data-test="topic"]').clear()
    if (topic !== '') cy.get('[data-test="topic"]').type(topic)
  }
  if (initialDate !== undefined) {
    cy.get('[data-test="date"]').clear()
    if (initialDate !== '') cy.get('[data-test="date"]').type(initialDate)
  }
  if (deadline !== undefined) {
    cy.get('[data-test="deadline"]').clear()
    if (deadline !== '') cy.get('[data-test="deadline"]').type(deadline)
  }
  if (comment !== undefined) {
    cy.get('[data-test="comment"]').clear()
    if (comment !== '') cy.get('[data-test="comment"]').type(comment)
  }
})

Cypress.Commands.add('stakeholder', (index,id, name, phone, email) => {
  cy.get('[data-test="stakeholder"]').eq(index).within(() => {
    if (id !== undefined) {
      cy.get('[data-test="id"]').clear()
      if (id !== '') cy.get('[data-test="id"]').type(id)
    }
    if (name !== undefined) {
      cy.get('[data-test="name"]').clear()
      if (name !== '') cy.get('[data-test="name"]').type(name)
    }
    if (phone !== undefined) {
        cy.get('[data-test="phone"]').clear()
        if (phone !== '') cy.get('[data-test="phone"]').type(phone)
    }
    if (email !== undefined) {
        cy.get('[data-test="email"]').clear()
        if (email !== '') cy.get('[data-test="email"]').type(email)
    }
  })
})

Cypress.Commands.add('removeStakeholder', (index) => {
  cy.get('[data-test="stakeholder"]').eq(index).within(() => {
    cy.get('[data-test="remove-stakeholder"]').first().click()
  })

})

//------------------ Data Inici --------------------------
Cypress.Commands.add('startingDate', () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const hours = String(today.getHours()).padStart(2, '0');
  const minutes = String(today.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${minutes}`;
});

//----------------- Data Final ---------------------------
Cypress.Commands.add('endingDate', (shiftMinutes = 0) => {
  const date = new Date();
  date.setMinutes(date.getMinutes() + shiftMinutes);

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${minutes}`;
});

before(() => {
  cy.startingDate().then((startDate) => {
    Cypress.env('visitStart', startDate);
  });

  cy.endingDate(60).then((endDate) => { 
    Cypress.env('visitEnd', endDate);
  });

});

Cypress.Commands.add('crearCita', () => {
  // Assumim que estem a un lloc on podem fer click al botó de crear cita
  cy.get('.action-item.icon-add').should('be.visible').click()
  // Omplim els valors del formulari
  cy.get('[data-test="procedure"]').select(0)
  cy.get('[data-test="cita-id"]').type('000111')
  cy.get('[data-test="department"]').select(0)
  cy.get('[data-test="user"]').select(0)
  cy.get('[data-test="channel"]').select(0)
  cy.get('[data-test="topic"]').type('topic')
  cy.get('[data-test="date"]').type(Cypress.env('visitStart'))
  cy.get('[data-test="deadline"]').type(Cypress.env('visitEnd'))
  cy.get('[data-test="comment"]').type('comentari prova')

  cy.stakeholder(0, '4894684f', 'prova', '4539746', 'prova@prova.com')
  cy.get('[data-test="save"]').click()

  // Esperem que el toast de success s'hagi amagat del tot
  cy.get('.toast-success').should('be.visible')
  cy.get('.toast-success', { timeout: 20000 }).should('not.exist')
})

Cypress.on('uncaught:exception', (err, runnable) => {
  if (err.message.includes('normalizeItems is not a function')) {
    return false
  }
})
