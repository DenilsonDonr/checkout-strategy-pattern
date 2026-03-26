# Checkout Strategy Pattern

A Laravel 13 application demonstrating the **Strategy Design Pattern** applied to a real-world e-commerce checkout flow. The system supports multiple interchangeable payment methods (Credit Card, PayPal, Bank Transfer, etc.) without modifying the core checkout logic.

---

## Architecture — C4 Model

Before writing a single line of code, the architecture was designed using the **C4 Model** (Context, Containers, Components, Code). This ensures a clear separation of concerns and a shared understanding of the system at every level of abstraction.

> Level 4 (Code) is planned and will be documented as implementation progresses.

---

### Level 1 — System Context

Defines who interacts with the system and what external systems it depends on.

![C4 Level 1 — System Context](docs/c4/level-1-dark.png)

---

### Level 2 — Container

Shows the high-level technology choices and how responsibilities are distributed across containers (Laravel + Inertia.js + Vue 3, database).

![C4 Level 2 — Container](docs/c4/level-2-dark.png)

---

### Level 3 — Component

Zooms into the API Application container and shows how the **Strategy Pattern** is structured internally — the `CheckoutContext`, `PaymentStrategyInterface`, and concrete strategy implementations.

![C4 Level 3 — Component](docs/c4/level-3-dark.png)

---

## Design Pattern — Strategy

The **Strategy Pattern** allows the payment method to be selected at runtime without changing the checkout flow. The `CheckoutContext` holds a reference to a `PaymentStrategyInterface` and delegates execution to whichever concrete strategy is injected.

```
PaymentStrategyInterface
    ├── CreditCardStrategy
    ├── PayPalStrategy
    └── BankTransferStrategy
```

This makes adding new payment methods a matter of implementing the interface — no existing code changes required (Open/Closed Principle).

---
