# Activity Diagram: Checkout Process

This diagram maps the logical decision-making flow for converting a cart into a finalized order.

```mermaid
graph TD
    Start([Start]) --> Auth[Validate JWT Token]
    Auth --> IsAuth{Token Valid?}
    
    IsAuth -- No --> Error401[Return 401 Unauthorized]
    IsAuth -- Yes --> FetchCart[Fetch Cart Items]
    
    FetchCart --> IsEmpty{Cart Empty?}
    IsEmpty -- Yes --> Error422a[Return 422 Cart Empty]
    IsEmpty -- No --> CheckStock[Check Product Stock]
    
    CheckStock --> StockOK{In Stock?}
    StockOK -- No --> Error422b[Return 422 Stock Error]
    StockOK -- Yes --> PayFactory[Initialize Payment Factory]
    
    PayFactory --> ProcessPay[Process Payment]
    ProcessPay --> PayOK{Success?}
    
    PayOK -- No --> Error402[Return 402 Payment Required]
    PayOK -- Yes --> CreateOrder[Create Order & Items]
    
    CreateOrder --> ClearCart[Empty User Cart]
    ClearCart --> Event[Dispatch OrderPlaced Event]
    Event --> Success[Return 201 Created]
    Success --> End([End])
```

> **Note:** Mermaid uses `flowchart` logic for activities if the renderer doesn't support the experimental `activityDiagram` keyword.
