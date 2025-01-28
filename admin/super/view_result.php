<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

$result_id = $_GET['result_id'] ?? null;

if (!$result_id) {
    header('Location: exam_results.php');
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get result details
    $query = "SELECT 
                er.*,
                e.title as exam_title,
                e.description as exam_description,
                e.type as exam_type,
                e.part as exam_part,
                e.passing_score,
                e.duration_minutes,
                a.*,
                CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                u.email,
                a.contact_number
              FROM exam_results er
              JOIN exams e ON er.exam_id = e.id
              JOIN applicants a ON er.applicant_id = a.id
              JOIN users u ON a.user_id = u.id
              WHERE er.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$result_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception('Result not found');
    }

    // Get question details and answers
    $query = "SELECT 
                q.*,
                aa.answer as applicant_answer,
                aa.is_correct,
                aa.score
              FROM questions q
              LEFT JOIN applicant_answers aa ON q.id = aa.question_id 
                AND aa.applicant_id = ? AND aa.exam_id = ?
              WHERE q.exam_id = ?
              ORDER BY q.id ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$result['applicant_id'], $result['exam_id'], $result['exam_id']]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics
    $totalQuestions = count($questions);
    $answeredQuestions = 0;
    $correctAnswers = 0;
    $totalScore = 0;
    $maxScore = 0;

    foreach ($questions as $question) {
        if ($question['applicant_answer'] !== null) {
            $answeredQuestions++;
        }
        if ($question['is_correct']) {
            $correctAnswers++;
        }
        $totalScore += $question['score'] ?? 0;
        $maxScore += $question['points'];
    }

    $scorePercentage = ($totalScore / $maxScore) * 100;
    $passed = $scorePercentage >= $result['passing_score'];

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

admin_header('View Exam Result');
?>

<div class="container-fluid">
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <!-- Result Overview -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">Exam Result Overview</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Applicant Information</h4>
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo htmlspecialchars($result['applicant_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td><?php echo htmlspecialchars($result['contact_number']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($result['email']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Exam Information</h4>
                        <table class="table table-sm">
                            <tr>
                                <th>Exam:</th>
                                <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                            </tr>
                            <tr>
                                <th>Part:</th>
                                <td><?php echo htmlspecialchars($result['exam_part']); ?></td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td><?php echo strtoupper(htmlspecialchars($result['exam_type'])); ?></td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td><?php echo htmlspecialchars($result['duration_minutes']); ?> minutes</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4>Result Summary</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Final Score</h6>
                                        <h2 class="mb-0"><?php echo number_format($scorePercentage, 1); ?>%</h2>
                                        <span class="badge <?php echo $passed ? 'bg-success' : 'bg-danger'; ?> mt-2">
                                            <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Questions</h6>
                                        <h2 class="mb-0"><?php echo $correctAnswers; ?>/<?php echo $totalQuestions; ?></h2>
                                        <small class="text-muted">Correct Answers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Points</h6>
                                        <h2 class="mb-0"><?php echo $totalScore; ?>/<?php echo $maxScore; ?></h2>
                                        <small class="text-muted">Total Points</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Completion Time</h6>
                                        <h2 class="mb-0"><?php echo date('h:i', strtotime($result['completion_time'])); ?></h2>
                                        <small class="text-muted"><?php echo date('A', strtotime($result['completion_time'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Questions and Answers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Detailed Responses</h3>
            </div>
            <div class="card-body">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-item mb-4 p-3 border rounded <?php echo $question['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="mb-3">
                                Question <?php echo $index + 1; ?>
                                <span class="badge <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?> ms-2">
                                    <?php echo $question['score'] ?? 0; ?>/<?php echo $question['points']; ?> points
                                </span>
                            </h5>
                            <span class="badge <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                            </span>
                        </div>

                        <div class="question-text mb-3">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </div>

                        <?php if ($question['question_type'] === 'multiple_choice'): ?>
                            <div class="options mb-3">
                                <?php 
                                $options = json_decode($question['options'], true);
                                foreach ($options as $option): 
                                ?>
                                    <div class="option">
                                        <i class="bi <?php 
                                            echo $option === $question['applicant_answer'] ? 
                                                ($question['is_correct'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger') : 
                                                'bi-circle'; 
                                        ?>"></i>
                                        <?php echo htmlspecialchars($option); ?>
                                        <?php if ($option === $question['correct_answer']): ?>
                                            <span class="badge bg-success">Correct Answer</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="code-answer mb-3">
                                <h6>Applicant's Answer:</h6>
                                <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($question['applicant_answer']); ?></code></pre>
                                
                                <?php if (!$question['is_correct']): ?>
                                    <h6 class="mt-3">Correct Answer:</h6>
                                    <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($question['correct_answer']); ?></code></pre>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($question['explanation']): ?>
                            <div class="explanation mt-3">
                                <h6>Explanation:</h6>
                                <p class="text-muted"><?php echo htmlspecialchars($question['explanation']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.option {
    margin: 8px 0;
    padding: 8px;
    border-radius: 4px;
    background-color: #f8f9fa;
}

.option i {
    margin-right: 8px;
}

.question-item {
    transition: all 0.3s ease;
}

.question-item:hover {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
</style>

<?php
admin_footer();
?>
