@extends('adlayout')

@section('adcontent')

<style>
    body {
        background: #f4f6f9;
        font-family: 'Segoe UI', sans-serif;
    }

    /* PAGE TITLE */
    .page-title {
        animation: slideInLeft 0.6s ease forwards;
    }

    /* MESSAGE CARD */
    .message-card {
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        animation: fadeUp 0.8s ease forwards;
        opacity: 0;
    }

    .message-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.15);
    }

    .message-header {
        background: linear-gradient(135deg, #1f6ae1, #3f87f5);
        color: #fff;
        padding: 16px;
    }

    .message-body {
        padding: 16px;
    }

    .btn-sm {
        border-radius: 20px;
        padding: 5px 14px;
    }

    /* ANIMATIONS */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-40px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>

<div class="container-fluid p-4">

    <!-- PAGE HEADER -->
    <div class="mb-4 page-title">
        <h4 class="fw-bold text-primary">
            <i class="bi bi-envelope"></i> Patient / Visitor Messages
        </h4>
        <p class="text-muted small">Messages received from contact form</p>
    </div>

    <!-- ALERTS -->
    @if(session('success'))
    <div class="alert alert-success auto-close show">{{ session('success') }}</div>

    @elseif(session('error'))
    <div class="alert alert-danger auto-close show">{{ session('error') }}</div>
    @endif
    <script>
        setTimeout(function() {
            let alerts = document.querySelectorAll('.auto-close');
            alerts.forEach(function(alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000); // closes after 3 seconds
    </script>

    <!-- MESSAGE GRID -->
    <div class="row g-4">

        @forelse($messages as $msg)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card message-card shadow-sm h-100">
                <div class="message-header p-3 border-bottom">
                    <h6 class="mb-1">{{ $msg->subject }}</h6>
                    <small class="text-muted">
                        {{ $msg->name }} • {{ $msg->created_at->format('d M Y') }}
                    </small>
                </div>

                <div class="message-body p-3">
                    <p class="small text-muted mb-1">
                        <strong>Email:</strong> {{ $msg->email }}
                    </p>

                    <p class="mb-3 text-truncate">
                        {{ $msg->message }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center">

                        <button class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#viewModal{{ $msg->id }}">
                            <i class="bi bi-eye"></i> View
                        </button>

                        {{-- SHOW REPLY BUTTON ONLY IF NOT REPLIED --}}
                        @if(empty($msg->reply))
                        <button class="btn btn-sm btn-outline-success"
                            data-bs-toggle="modal"
                            data-bs-target="#replyModal{{ $msg->id }}">
                            <i class="bi bi-reply"></i> Reply
                        </button>
                        @else
                        <span class="badge bg-success">Replied</span>
                        @endif

                        <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal{{ $msg->id }}">
                            <i class="bi bi-trash"></i>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- ================= VIEW MESSAGE MODAL ================= -->
        <div class="modal fade" id="viewModal{{ $msg->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-envelope-open"></i> Message Details
                        </h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p><strong>Name:</strong> {{ $msg->name }}</p>
                        <p><strong>Email:</strong> {{ $msg->email }}</p>
                        <p><strong>Subject:</strong> {{ $msg->subject }}</p>
                        <hr>
                        <p>{{ $msg->message }}</p>
                        @if(!empty($msg->reply))
                        <hr>
                        <h6 class="text-success">Admin Reply:</h6>
                        <p>{{ $msg->reply }}</p>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= REPLY MESSAGE MODAL ================= -->
        <div class="modal fade" id="replyModal{{ $msg->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 shadow">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-reply"></i> Reply to Message
                        </h5>
                        <button class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="{{ route('admin.contact.reply') }}">
                        @csrf

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">To (Email)</label>
                                    <input type="email" class="form-control"
                                        name="email" value="{{ $msg->email }}" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Subject</label>
                                    <input type="text" class="form-control"
                                        name="subject" value="Re: {{ $msg->subject }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message</label>
                                    <textarea class="form-control" name="message"
                                        rows="5" placeholder="Type your reply here..."
                                        required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button"
                                class="btn btn-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-send"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= DELETE CONFIRM MODAL ================= -->
        <div class="modal fade" id="deleteModal{{ $msg->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-trash"></i> Confirm Delete
                        </h5>
                        <button class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body text-center p-4">
                        <p class="mb-0">
                            Are you sure you want to delete this message?
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <form method="POST"
                            action="{{ route('admin.contact.delete', $msg->id) }}">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button class="btn btn-danger rounded-pill px-4">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No messages found.
            </div>
        </div>
        @endforelse

    </div>
</div>


@endsection