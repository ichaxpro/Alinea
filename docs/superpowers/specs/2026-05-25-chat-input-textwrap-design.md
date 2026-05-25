# Chat Input Text Wrapping

**Date:** 2026-05-25

## Goal

Replace the single-line `<input type="text">` chat input with an auto-resizing `<textarea>` that supports multi-line text wrapping.

## Current State

The chat input at `resources/views/chat.blade.php:228` uses `<input type="text">` which physically cannot wrap text or contain newlines. Enter sends the message; there is no multi-line support.

The project already uses `data-autogrow` textareas with auto-resize logic in `timeline.js:163-164` for post composition.

## Changes

### 1. HTML — Replace `<input>` with `<textarea>`

**File:** `resources/views/chat.blade.php:228-229`

Replace `<input id="messageInput" type="text">` with `<textarea id="messageInput" rows="1" data-autogrow>`, keeping the same Tailwind utility classes except:

- `rounded-full` → `rounded-2xl` (pill shape doesn't work for a multi-line element)
- Add `resize-none` (prevent manual resize)
- Add `overflow-y-auto` (scroll when content exceeds max-height)
- Add `max-h-32` (limit growth to ~4-5 lines)

### 2. Layout — Bottom-align buttons

**File:** `resources/views/chat.blade.php:202`

Change `#chatInputArea` from `items-center` to `items-end` so the send/emoji/attachment buttons stay aligned to the bottom of the input bar as the textarea grows taller.

### 3. CSS — Update focus and scrollbar styles

**File:** `resources/views/chat.blade.php:77-79`

Keep the existing `#messageInput:focus` and `#messageInput` transitions. Add scrollbar styling for the textarea.

### 4. JS — Auto-resize handler

**File:** `resources/js/chat.js`

Add an `input` event handler that resizes the textarea height:
```js
el.style.height = 'auto';
el.style.height = el.scrollHeight + 'px';
```

This follows the existing pattern in `timeline.js:163-164`.

### 5. JS — Send behavior

**File:** `resources/js/chat.js:538-539`

The existing handler already checks `!e.shiftKey`, so Enter sends and Shift+Enter inserts a newline. No change needed.

### 6. JS — Reset height after send

**File:** `resources/js/chat.js`

After `sendMessage()` clears the input value, also reset the textarea height to its initial single-line state.

### 7. JS — Emoji picker

The emoji click handler appends to `messageInput.value` which works identically for textarea. No change needed.

## Behavior

- Single line when empty, auto-grows as user types
- Max ~4 lines (`max-h-32` ≈ 128px), then scrollbar appears
- Enter sends message, Shift+Enter inserts newline
- Send/emoji/attachment buttons stay bottom-aligned
- After sending, textarea resets to single line
